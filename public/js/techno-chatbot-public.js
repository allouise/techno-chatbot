/**
 * Techno Chatbot Public Script
 * States:
 * 0 - Initial/Reset/Idle State
 * 1 - No Answer/Contact Options Shown
 * 2 - Contact Method Selected (Phone/Email)
 * 3 - Phone Follow-up step
 * 4 - Contact Finished/Handoff complete
 * 5 - Livechat Active
 * 6 - Ask Visitor name before live chat
 * 7 - End Chat By Admin show options
 * 8 - Ask Email For Chat History
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ---------- Elements ---------- */
    const el = {
        icon: document.getElementById('techno-chatbot-floating-icon'),
        window: document.getElementById('techno-chatbot-window'),
        close: document.getElementById('techno-chatbot-close'),
        send: document.getElementById('techno-chatbot-send'),
        input: document.getElementById('techno-chatbot-input'),
        messages: document.getElementById('techno-chatbot-messages'),
        menubtn: document.getElementById('techno-chatbot-menu-trigger'),
        reset: document.querySelectorAll('.techno-chatbot-reset'),
        disclaimer: document.getElementById('techno-chatbot-disclaimer'),
        disclaimerModal: document.getElementById('techno-chatbot-disclaimer-modal'),
    };

    if (!el.icon || !el.window) return;

    /* ---------- Constants ---------- */
    const STORAGE_KEY = 'techno_chatbot_history';
    const FAIL_COUNT_KEY = 'techno_chatbot_fail_count';
    const CONTACT_STATE_KEY = 'techno_chatbot_contact_state';
    const CONTACT_METHOD_KEY = 'techno_chatbot_contact_method';
    const CHAT_START_KEY = 'techno_chatbot_start';
    const LIVECHAT_NAME_KEY = 'techno_livechat_visitor_name';
    const LIVECHAT_SESSION = 'techno_livechat_session';
    const LIVECHAT_IDLE = 'techno_livechat_idled';

    /* ---------- State ---------- */
    let socket = null;
    let liveChatSessionId = null;
    let liveChatVisitorName = localStorage.getItem(LIVECHAT_NAME_KEY) || null;
    let chatHistory = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    let supportOnline = false;
    let idleDisconnectTimer = localStorage.getItem(LIVECHAT_IDLE) || null;;

    if (!localStorage.getItem(CHAT_START_KEY)) {
        localStorage.setItem(CHAT_START_KEY, Date.now());
    }

    /* ---------- Utilities ---------- */
    function scrollToBottom() {
        el.messages.scrollTop = el.messages.scrollHeight;
    }
    function validateEmail(email){
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    function validatePhone(phone){
        const cleaned = phone.replace(/[\s\-()+]/g,'');
        if (!/^\d{7,15}$/.test(cleaned)) return false;
        return true;
    }
    function cleanText(text){
        return text.toLowerCase().replace(/[^\w\s]/g,'').trim();
    }
    function sanitizeText(text){
        if (typeof text !== 'string') return '';
        text = text.replace(/<[^>]*>?/gm, '').replace(/[\u0000-\u001F]/g, '').replace(/\s+/g,' ').trim();
        if (text.length > 1000){
            text = text.substring(0,1000);
        }
        return text;
    }
    function showTyping() {
        const typing = document.createElement('div');
        typing.className = 'techno-chatbot-message admin typing';
        typing.innerHTML = `<span></span><span></span><span></span>`;
        el.messages.appendChild(typing);
        scrollToBottom();
        return typing;
    }
    function getTypingDelay(text) {
        const base = 500;
        const perChar = 0; /* optional per char delay */
        const max = 3000;
        return Math.min(base + text.length * perChar, max);
    }
    function disableInput(_switch = true){
        el.input.disabled = _switch;
        el.send.disabled = _switch;
        if (!_switch) el.input.focus();
    }
    function setState(state){
        localStorage.setItem(CONTACT_STATE_KEY, state);
    }
    function getState(){
        return parseInt(localStorage.getItem(CONTACT_STATE_KEY) || '0');
    }
    function clearIdleDisconnectTimer() {
        if (idleDisconnectTimer) {
            clearTimeout(idleDisconnectTimer);
            idleDisconnectTimer = null;
            localStorage.removeItem(LIVECHAT_IDLE);
        }
    }
    function startIdleDisconnectTimer() {
        if(!technoChatbot.idleTimer || technoChatbot.idleTimer == '') return;
        const idleSeconds = parseInt(technoChatbot.idleTimer);
        if (!idleSeconds || idleSeconds <= 0) return;
        if (getState() !== 5) return;

        clearIdleDisconnectTimer();
        idleDisconnectTimer = setTimeout(async () => {
            if (!socket || !socket.connected || !supportOnline) {
                if (!document.querySelector('.techno-chatbot-contact-options')) {
                    await botReply(technoChatbot.idleSupport);
                    showNoAnswerOptions();
                    //setState(0);
                }
            }
        }, idleSeconds * 1000);
    }

    /* ---------- Histories ---------- */
    function saveHistory(text, sender) {
        text = sanitizeText(text);
        if (!text) return;
        chatHistory.push({ text, sender, created_at: new Date().toISOString() });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(chatHistory));
        const state = getState();
        if ( (state >= 5 || idleDisconnectTimer ) && liveChatSessionId && sender != 'admin' ){
            saveMessageToDB(liveChatSessionId, sender, text);
        }
    }
    function clearHistory(prependMsg = null) {
        if (socket) {
            if (liveChatSessionId) {
                socket.emit("restarted-leave", { session_id: liveChatSessionId });
            }
            socket.off("disconnect");
            socket.disconnect();
            socket = null;
        }

        localStorage.removeItem(STORAGE_KEY);
        localStorage.removeItem(CONTACT_STATE_KEY);
        localStorage.removeItem(CONTACT_METHOD_KEY);
        localStorage.removeItem(FAIL_COUNT_KEY);
        localStorage.removeItem(CHAT_START_KEY);
        localStorage.removeItem(LIVECHAT_NAME_KEY);
        localStorage.removeItem(LIVECHAT_SESSION);
        localStorage.removeItem(LIVECHAT_IDLE);
        idleDisconnectTimer = null;
        liveChatSessionId = null;
        liveChatVisitorName = null;
        chatHistory = [];
        el.messages.innerHTML = '';

        if (prependMsg) {
            const div = document.createElement('div');
            div.className = 'techno-chatbot-message admin';
            div.textContent = prependMsg;
            el.messages.appendChild(div);
        }

        if (technoChatbot.disclaimerEnabled == 1 && technoChatbot.disclaimerMsg) {
            botReply(technoChatbot.disclaimerMsg).then(() => {
                botReply(technoChatbot.welcomeMessage);
            });
        } else {
            botReply(technoChatbot.welcomeMessage);
        }
        disableInput(false);
        if (!socket){ initSocket(); } 
    }
    function loadHistory(){
        chatHistory.forEach(msg => addMessage(msg.text, msg.sender, false));
        const state = getState();
        if(state === 1) showNoAnswerOptions(true);
        if(state === 2 || state === 3) disableInput(false);
        if(state === 5){
            setTimeout(() => {
                if(socket && liveChatSessionId){
                    socket.emit("visitor-join", { session_id: liveChatSessionId });
                }
            }, 500);
        }
        if (state === 6) {
            if (parseInt(technoChatbot.liveChatGetName) === 1) {
                disableInput(false);
            } else {
                setState(0);
                disableInput(false);
            }
        }
        if(state === 7) showEndChatOptions(true);
        if(state === 8) disableInput(false);
        scrollToBottom();
    }
    function botHistoryToLive(){
        const history = localStorage.getItem(STORAGE_KEY);

        if (!history) return;
        fetch(technoChatbot.ajax_url,{
            method:'POST',
            headers:{
                'Content-Type':
                'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'techno_bot_to_live',
                nonce: technoChatbot.nonce,
                session_id: liveChatSessionId,
                history: history
            })
        })
        .catch(err =>
            console.error(
                '[Chatbot] History transfer failed:',
                err
            )
        );
    }

    /* ---------- FAQ Handling ---------- */
    const processedFaq = technoChatbot.faq.map(faq => ({
        ...faq,
        cleanedQuestions: faq.questions.map(q => ({
            original: q,
            cleaned: cleanText(q),
            words: cleanText(q).split(' ').filter(w => w.length >= 4)
        }))
    }));
    async function handleFaqReply(message){
        const answer = findFaqAnswer(message);
        const failLimit = parseInt(technoChatbot.noAnswerTrigger) || 0;
        let failCount = parseInt(localStorage.getItem(FAIL_COUNT_KEY) || '0');

        if(technoChatbot.transferKeywords && technoChatbot.transferKeywords.length){
            const text = cleanText(message);
            for(const keyword of technoChatbot.transferKeywords){
                if(keyword && text.includes(cleanText(keyword))){
                    failCount = failLimit;
                    localStorage.setItem(FAIL_COUNT_KEY, failCount);
                    break;
                }
            }
        }

        if(answer === technoChatbot.noAnswer){
            failCount++;
            localStorage.setItem(FAIL_COUNT_KEY, failCount);
            if(failCount >= failLimit){
                await botReply(technoChatbot.noAnswerFinalDefault || technoChatbot.noAnswer || '...');
                showNoAnswerOptions();
                localStorage.setItem(FAIL_COUNT_KEY, 0);
            } else {
                await botReply(answer);
            }
        } else {
            localStorage.setItem(FAIL_COUNT_KEY, 0);
            await botReply(answer);
        }
    }
    function findFaqAnswer(message){
        const text = cleanText(message);
        let bestMatch = null;
        let bestScore = 0;

        for (const faq of processedFaq) {
            let score = 0;
            for (const kw of faq.cleanedQuestions) {
                if (text.includes(kw.cleaned)) score += 5;
                kw.words.forEach(word => {
                    if (text.includes(word)) score += 2;
                });
                const similarity = stringSimilarity(text, kw.cleaned);
                if (similarity > 0.8) score += similarity * 5;
            }
            score += (faq.priority || 0);
            if (score > bestScore) {
                bestScore = score;
                bestMatch = faq;
            }
        }

        return (bestMatch && bestScore >= 6) ? bestMatch.answer : technoChatbot.noAnswer;
    }
    function stringSimilarity(str1, str2){
        const longer = str1.length > str2.length ? str1 : str2;
        const shorter = str1.length > str2.length ? str2 : str1;
        if(longer.length === 0) return 1;

        return (longer.length - levenshteinDistance(longer, shorter)) / longer.length;
    }
    function levenshteinDistance(a, b){
        const matrix = Array(b.length + 1).fill(null).map(() => Array(a.length + 1).fill(0));
        for(let i=0;i<=b.length;i++) matrix[i][0]=i;
        for(let j=0;j<=a.length;j++) matrix[0][j]=j;

        for(let i=1;i<=b.length;i++){
            for(let j=1;j<=a.length;j++){
                matrix[i][j] = b[i-1] === a[j-1]
                    ? matrix[i-1][j-1]
                    : Math.min(matrix[i-1][j-1]+1, matrix[i][j-1]+1, matrix[i-1][j]+1);
            }
        }
        return matrix[b.length][a.length];
    }

    /* ---------- WebSocket Live Chat ---------- */
    function initSocket() {
        if(!technoChatbot.ws_url) return;
        if (socket) return;

        socket = io(technoChatbot.ws_url, { 
            transports: ['polling', 'websocket'],
            secure: true,
            /* reconnection: false, */
            auth: {
                site: technoChatbot.site_id,
                token: technoChatbot.token
            }
        });
        socket.on("connect", () => {
            console.log("WS connected:", socket.id);
            clearIdleDisconnectTimer();
            liveChatSessionId = localStorage.getItem(LIVECHAT_SESSION);
            if(liveChatSessionId){
                socket.emit("visitor-join", {
                    session_id: liveChatSessionId,
                    visitor_name: liveChatVisitorName || liveChatSessionId
                });
            }
        });

        socket.on("receive-message", async (msg) => {
            if (msg.session_id !== liveChatSessionId) return;

            if (msg.sender === 'admin') {
                if (msg.message.trim() === '/endchat') {
                    await handleEndChatCommand();
                    return;
                }
                addMessage(msg.message, 'admin');
            }
        });

        socket.on("support-status", (data) => {
            if(typeof data.online === 'boolean'){
                updateStatusDot(data.online);
                if (data.online === false) {
                    startIdleDisconnectTimer();
                } else {
                    clearIdleDisconnectTimer();
                }
            }
        });

        socket.on("unregister-support", () => {
            updateStatusDot(false);
            startIdleDisconnectTimer();
        });

        socket.on("disconnect", () => {
            console.log("WS disconnected");
            updateStatusDot(false);
            startIdleDisconnectTimer();
        });
    }
    function updateStatusDot(online) {
        supportOnline = online;
        const dot = document.getElementById('techno-support-status-dot');
        if (!dot) return;
        dot.classList.toggle('online', online);
        dot.classList.toggle('offline', !online);
        dot.title = online ? 'Support Online' : 'Support Offline';
    }
    async function handleOnlineStatus(online) {
        if (online) {
            if (parseInt(technoChatbot.liveChatGetName) === 1) {
                setState(6);
                await botReply(technoChatbot.getName);
                disableInput(false);
            } else {
                setState(5);
                await botReply(technoChatbot.transferredToSupport);
                await startLiveChat();
            }
        } else {
            setState(0);
            await botReply(technoChatbot.offlineSupport);
            showNoAnswerOptions();
        }
    }
    async function startLiveChat() {
        liveChatSessionId = localStorage.getItem(LIVECHAT_SESSION) || ('sess_' + Date.now());
        localStorage.setItem(LIVECHAT_SESSION, liveChatSessionId);
        disableInput(false);
        if (!socket){ initSocket(); } 
        botHistoryToLive();
        if (socket.connected) {
            socket.emit("visitor-join", { session_id: liveChatSessionId, visitor_name: liveChatVisitorName || liveChatSessionId });
        }
    }
    async function checkAndTransferToLiveChat() {
        if (!technoChatbot.liveChatEnabled) {
            await botReply(technoChatbot.offlineSupport);
            showNoAnswerOptions();
            return;
        }

        if (supportOnline) {
            handleOnlineStatus(supportOnline);
            return;
        }

        try {
            const res = await fetch(technoChatbot.ajax_url, {
                method:'POST',
                headers:{ 'Content-Type':'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action:'techno_check_support_online',
                    nonce: technoChatbot.nonce
                })
            });
            const data = await res.json();
            const online = data.success && data.data.online;
            updateStatusDot(online);
            handleOnlineStatus(online);
        } catch(e){
            console.error(e);
            setState(0);
            await botReply(technoChatbot.offlineSupport);
            showNoAnswerOptions();
        }
    }
    async function handleEndChatCommand(){
        if (socket && liveChatSessionId) {
            socket.emit("visitor-leave", {
                session_id: liveChatSessionId
            });
        }
        setState(7);
        await botReply(technoChatbot.end_msg);
        showEndChatOptions();
    }
    function showEndChatOptions(restored = false){
        if (document.querySelector('.techno-chatbot-end-options')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'techno-chatbot-end-options';

        /* YES BUTTON */
        const yesBtn = document.createElement('button');
        yesBtn.textContent = technoChatbot.menuHistorySend;
        yesBtn.onclick = () => {
            wrapper.remove();
            addMessage( technoChatbot.menuHistorySend, 'visitor', true );
            askEmailForHistory();
        };

        /* NO BUTTON */
        const noBtn = document.createElement('button');
        noBtn.textContent = technoChatbot.menuLeave;
        noBtn.onclick = () => {
            wrapper.remove();
            addMessage( technoChatbot.menuLeave, 'visitor', true );
            finishLiveChat();
        };

        wrapper.appendChild(yesBtn);
        wrapper.appendChild(noBtn);
        el.messages.appendChild(wrapper);
        scrollToBottom();
        disableInput(true);
        el.input.placeholder = 'Choose an option...';
        if(!restored) setState(7);
    }
    async function askEmailForHistory(){
        setState(8);
        disableInput(false);
        await botReply( technoChatbot.askEmail );
    }
    function finishLiveChat(email = null){
        const history = localStorage.getItem(STORAGE_KEY);
        if (!history) return;
        fetch(technoChatbot.ajax_url, {
            method:'POST',
            headers:{
                'Content-Type':
                'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'end_live_chat',
                history: history,
                email: email || '',
                nonce: technoChatbot.nonce
            })
        })
        .then(res => res.json())
        .then(async data => {
            if (data.success) {
                if( email ){
                    await botReply( technoChatbot.historySent );
                }else{
                    await botReply( technoChatbot.endChatMsg );
                }
            } else {
                await botReply( technoChatbot.errorMsg );
            }
            endLiveChatCleanup();
        })
        .catch(async () => {
            await botReply(
                technoChatbot.errorMsg
            );
            endLiveChatCleanup();
        });
    }
    function endLiveChatCleanup(){
        setState(0);
        if (socket && liveChatSessionId) {
            socket.emit( "visitor-leave", { session_id: liveChatSessionId } );
            socket.disconnect();
            socket = null;
            chatHistory = [];
        }
        clearIdleDisconnectTimer();
    }

    /* ---------- Chatbot Send Handler ---------- */
    let isProcessing = false;
    const handleSend = async () => {
        if (isProcessing) return;
        isProcessing = true;
        
        const userInput = el.input.value.trim();
        const userMessage = sanitizeText(userInput);
        if(!userMessage){ isProcessing = false; return; }

        addMessage(userMessage, 'visitor');
        el.input.value = '';
        const state = getState();

        if(state === 2){
            const method = getContactMethod();
            if(method === 'phone'){
                const phone = userMessage;
                if (!validatePhone(phone)) {
                    await botReply( technoChatbot.phoneError );
                    isProcessing = false;
                    return;
                }
                if(parseInt(technoChatbot.timeToCall) === 1){
                    setState(3);
                    await botReply( technoChatbot.timeToCallTxt );
                } else {
                    await finishContact();
                }
                isProcessing = false;
                return;
            }
            if(method === 'email'){
                const email = userMessage;
                if (!validateEmail(email)) {
                    await botReply( technoChatbot.emailError );
                    isProcessing = false;
                    return;
                }
                await finishContact();
                isProcessing = false;
                return;
            }
        }

        if(state === 3){
            await finishContact();
            isProcessing = false;
            return;
        }

        if(state === 5 && socket && socket.connected){
            socket.emit("send-message", {
                session_id: liveChatSessionId,
                message: userMessage,
                sender: "visitor"
            });
            isProcessing = false;
            return;
        }

        if(state === 6){
            liveChatVisitorName = userMessage;
            localStorage.setItem(LIVECHAT_NAME_KEY, liveChatVisitorName);
            setState(5);
            await botReply(technoChatbot.transferredToSupport);
            await startLiveChat();
            isProcessing = false;
            return;
        }

        if(state === 8){
            const email = userMessage;
            if (!validateEmail(email)) {
                await botReply('Please enter a valid email address.');
                isProcessing = false;
                return;
            }
            await finishLiveChat(email);
            isProcessing = false;
            return;
        }

        await handleFaqReply(userMessage);
        isProcessing = false;
    };
    function addMessage(text, sender, save = true) {
        const message = document.createElement('div');
        let cssClass = sender;
        if (sender === 'bot') cssClass = 'bot';
        if (sender === 'admin') cssClass = 'admin';
        if (sender === 'visitor') cssClass = 'visitor';
        message.className = `techno-chatbot-message ${cssClass}`;
        message.textContent = text;

        /* Time */
        const time = document.createElement('div');
        time.className = 'techno-chatbot-time';
        time.textContent = new Date().toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
        message.appendChild(time);

        el.messages.appendChild(message);
        scrollToBottom();
        if (save) saveHistory(text, sender);
    }
    function botReply(text) {
        if(!text) return Promise.resolve();
        disableInput(true);
        el.input.placeholder = 'Please wait...';
        const typing = showTyping();
        const delay = getTypingDelay(text);
        return new Promise(resolve => {
            setTimeout(() => {
                typing.remove();
                addMessage(text, 'bot');
                resolve();
                disableInput(false);
                el.input.placeholder = technoChatbot.inputtxt;
            }, delay);
        });
    }
    function saveMessageToDB(sessionId, sender, message) {
        if (!sessionId || !sender || !message) return;
        const body = new URLSearchParams({
            action:       'techno_save_chat_message',
            nonce:        technoChatbot.nonce,
            session_id:   sessionId,
            sender:       sender,
            message:      message,
        });
        if (liveChatVisitorName && sender === 'visitor') {
            body.append('visitor_name', liveChatVisitorName);
        }
        fetch(technoChatbot.ajax_url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body,
            keepalive: true,
        }).catch(err => console.error('[Techno Chatbot] DB save failed:', err));
    }
    function sendChatToAdmin(){
        const started = parseInt(localStorage.getItem(CHAT_START_KEY));
        const duration = Date.now() - started;
        if(duration < 5000){
            botReply(technoChatbot.spamLimitMsg);
            return;
        }

        const lastMsg = technoChatbot.getContactThxMsg;
        const history = localStorage.getItem(STORAGE_KEY);
        if (!history) return;
        try {
            fetch(technoChatbot.ajax_url, {
                method:'POST',
                headers:{ 'Content-Type':'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action:'send_history_admin',
                    history: history,
                    nonce: technoChatbot.nonce
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) clearHistory(lastMsg);
                else botReply(technoChatbot.cerrorMsg);
            })
            .catch(err => {
                console.error(err);
                botReply(technoChatbot.errorMsg);
            });
        } catch(e){
            console.error(e);
            botReply(technoChatbot.cerrorMsg);
        } 
    }
    function showNoAnswerOptions(restored = false){
        if(document.querySelector('.techno-chatbot-contact-options')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'techno-chatbot-contact-options';

        if (technoChatbot.liveChatEnabled && supportOnline) {
            const livechatBtn = document.createElement('button');
            livechatBtn.textContent = technoChatbot.menuLivechat;
            livechatBtn.onclick = () => {
                wrapper.remove();
                addMessage(technoChatbot.menuLivechat, 'visitor', true);
                checkAndTransferToLiveChat();
            };
            wrapper.appendChild(livechatBtn);
        }

        const phoneBtn = document.createElement('button');
        phoneBtn.textContent = technoChatbot.menuCall;
        phoneBtn.onclick = () => chooseContact('phone');

        const emailBtn = document.createElement('button');
        emailBtn.textContent = technoChatbot.menuEmail;
        emailBtn.onclick = () => chooseContact('email');

        wrapper.appendChild(phoneBtn);
        wrapper.appendChild(emailBtn);

        if( !idleDisconnectTimer ){
            const restartBtn = document.createElement('button');
            restartBtn.textContent = technoChatbot.menuReset;
            restartBtn.onclick = () => {
                wrapper.remove();
                clearHistory();
            };
            wrapper.appendChild(restartBtn);
        }
        
        el.messages.appendChild(wrapper);
        scrollToBottom();
        disableInput(true);
        el.input.placeholder = 'Choose an option...';
        if(!restored) setState(1);
    }
    function chooseContact(method){
        setContactMethod(method);
        setState(2);
        
        const options = document.querySelector('.techno-chatbot-contact-options');
        if(options) options.remove();

        disableInput(false);

        const choiceLabel = method === 'phone' ? technoChatbot.menuCall : technoChatbot.menuEmail;
        addMessage(choiceLabel, 'visitor', true);

        const methodLabel = method === 'phone' ? technoChatbot.cPhoneLabel : technoChatbot.cEmailLabel;
        addMessage(methodLabel, 'bot', true);
    }
    function setContactMethod(method){
        localStorage.setItem(CONTACT_METHOD_KEY, method);
    }
    function getContactMethod(){
        return localStorage.getItem(CONTACT_METHOD_KEY);
    }
    async function finishContact(){
        setState(4);
        await botReply(technoChatbot.getContactThxMsg);
        sendChatToAdmin();
    }

    /* ---------- Event Listeners ---------- */
    el.send?.addEventListener('click', handleSend);
    el.input?.addEventListener('keypress', (e) => {
        if(e.key === 'Enter') handleSend();
    });
    el.close?.addEventListener('click', () => {
        el.window.classList.add('techno-chatbot-hidden');
    });
    el.icon.addEventListener('click', () => {
        el.window.classList.toggle('techno-chatbot-hidden');
        if(!el.window.classList.contains('techno-chatbot-hidden')){
            scrollToBottom();
            const history = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            if (!history.length) {
                if (technoChatbot.disclaimerEnabled == 1 && technoChatbot.disclaimerMsg) {
                    botReply(technoChatbot.disclaimerMsg);
                }
                botReply(technoChatbot.welcomeMessage);
            }
        }
    });
    el.menubtn.addEventListener('click', () => el.menubtn.classList.toggle('active'));
    if(el.reset.length > 0){
        el.reset.forEach(btn => btn.addEventListener('click', () => { clearHistory(); el.menubtn.classList.remove('active'); }));
    }
    if(el.disclaimer){
        el.disclaimer.addEventListener('click', () => {
            el.disclaimerModal.classList.add('active');
            el.menubtn.classList.remove('active');
        });
        el.disclaimerModal.querySelector('.close-btn').onclick = () => el.disclaimerModal.classList.remove('active');
    }
    /* TO FIX THIS SHOULD ONLY RUN ON LIVECHAT */
    /* window.addEventListener("storage", (e) => {
        if (e.key === STORAGE_KEY) {
            chatHistory = JSON.parse(e.newValue || '[]');
            el.messages.innerHTML = '';
            loadHistory();
        }
    }); */

    if (!socket){ initSocket(); } 
    loadHistory();
});