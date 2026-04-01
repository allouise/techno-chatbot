/**
 * Techno Chatbot Public Script
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
        menulist: document.getElementById('techno-chatbot-menu-list'),
        reset: document.querySelectorAll('.techno-chatbot-reset')
    };

    if (!el.icon || !el.window) return;

    /* ---------- Constants ---------- */
    const STORAGE_KEY = 'techno_chatbot_history';
    const FAIL_COUNT_KEY = 'techno_chatbot_fail_count';
    const CONTACT_STATE_KEY = 'techno_chatbot_contact_state';
    const CONTACT_METHOD_KEY = 'techno_chatbot_contact_method';
    const CHAT_START_KEY = 'techno_chatbot_start';
    const LIVECHAT_NAME_KEY = 'techno_livechat_visitor_name';

    /* ---------- State ---------- */
    let socket = null;
    let liveChatSessionId = null;
    let liveChatVisitorName = localStorage.getItem(LIVECHAT_NAME_KEY) || null;
    let statusDotCache = { online: false, ts: 0 };

    if(!localStorage.getItem(CHAT_START_KEY)){
        localStorage.setItem(CHAT_START_KEY, Date.now());
    }

    /* ---------- Utilities ---------- */
    function scrollToBottom() {
        el.messages.scrollTop = el.messages.scrollHeight;
    }

    function saveHistory(text, sender) {
        const history = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        history.push({ text, sender });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(history));
    }

    function addMessage(text, sender, save = true) {
        const message = document.createElement('div');
        message.className = `techno-chatbot-message ${sender}`;
        message.textContent = text;
        el.messages.appendChild(message);
        scrollToBottom();
        if (save) saveHistory(text, sender);
    }

    function cleanText(text){
        return text.toLowerCase().replace(/[^\w\s]/g,'').trim();
    }

    function showTyping() {
        const typing = document.createElement('div');
        typing.className = 'techno-chatbot-message admin typing';
        typing.innerHTML = `<span></span><span></span><span></span>`;
        el.messages.appendChild(typing);
        scrollToBottom();
        return typing;
    }

    function botReply(text) {
        if(!text) return Promise.resolve();
        const typing = showTyping();
        const delay = getTypingDelay(text);
        return new Promise(resolve => {
            setTimeout(() => {
                typing.remove();
                addMessage(text, 'admin');
                resolve();
            }, delay);
        });
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
    }

    function setState(state){
        localStorage.setItem(CONTACT_STATE_KEY, state);
    }

    function getState(){
        return parseInt(localStorage.getItem(CONTACT_STATE_KEY) || '0');
    }

    function setContactMethod(method){
        localStorage.setItem(CONTACT_METHOD_KEY, method);
    }

    function getContactMethod(){
        return localStorage.getItem(CONTACT_METHOD_KEY);
    }

    function updateStatusDot(online) {
        const dot = document.getElementById('techno-support-status-dot');
        if (!dot) return;
        dot.classList.toggle('online', online);
        dot.classList.toggle('offline', !online);
        dot.title = online ? 'Support Online' : 'Support Offline';
    }

    /* ---------- FAQ Handling ---------- */
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
                const nextStep = parseInt(technoChatbot.nextStep);
                let finalMsg;
                if(nextStep === 0){
                    finalMsg = technoChatbot.noAnswerFinalContact;
                } else if(nextStep === 2){
                    finalMsg = technoChatbot.noAnswerFinalLivechat;
                } else {
                    finalMsg = technoChatbot.noAnswerFinalDefault;
                }

                await botReply(finalMsg || technoChatbot.noAnswer || '...');
                if(nextStep === 0){
                    showContactOptions();
                } else if(nextStep === 1){
                    /* just show message */
                } else if(nextStep === 2){
                    await checkAndTransferToLiveChat();
                }
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

        for (const faq of technoChatbot.faq){
            let score = 0;
            for (const keyword of faq.questions){
                const cleanKeyword = cleanText(keyword);
                if(text.includes(cleanKeyword)) score += 5;
                cleanKeyword.split(' ').forEach(word => {
                    if(word.length >= 4 && text.includes(word)) score += 2;
                });
                const similarity = stringSimilarity(text, cleanKeyword);
                if(similarity > 0.8) score += similarity * 5;
            }
            score += (faq.priority || 0);
            if(score > bestScore){
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

    /* ---------- Contact Options ---------- */
    function showContactOptions(restored = false){
        if(document.querySelector('.techno-chatbot-contact-options')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'techno-chatbot-contact-options';

        const phoneBtn = document.createElement('button');
        phoneBtn.textContent = 'Phone';

        const emailBtn = document.createElement('button');
        emailBtn.textContent = 'Email';

        wrapper.appendChild(phoneBtn);
        wrapper.appendChild(emailBtn);

        el.messages.appendChild(wrapper);
        scrollToBottom();
        disableInput(true);

        if(!restored) setState(1);

        phoneBtn.onclick = () => chooseContact('phone');
        emailBtn.onclick = () => chooseContact('email');
    }

    function chooseContact(method){
        setContactMethod(method);
        setState(2);

        const options = document.querySelector('.techno-chatbot-contact-options');
        if(options) options.remove();

        disableInput(false);

        const methodLabel = method === 'phone' ? technoChatbot.cPhoneLabel : technoChatbot.cEmailLabel;
        addMessage(methodLabel, 'admin', true);
    }

    async function finishContact(){
        setState(4);
        await botReply(technoChatbot.getContactThxMsg);
        sendChatToAdmin();
    }

    function sendChatToAdmin(){
        const started = parseInt(localStorage.getItem(CHAT_START_KEY));
        const duration = Date.now() - started;
        if(duration < 5000){
            botReply(technoChatbot.spamLimitMsg);
            return;
        }

        const history = localStorage.getItem(STORAGE_KEY);
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
            if(data.success) clearHistory();
            else botReply(technoChatbot.cerrorMsg);
        })
        .catch(err => {
            console.error(err);
            botReply(technoChatbot.errorMsg);
        });
    }

    function clearHistory() {
        localStorage.removeItem(STORAGE_KEY);
        localStorage.removeItem(CONTACT_STATE_KEY);
        localStorage.removeItem(CONTACT_METHOD_KEY);
        localStorage.removeItem(FAIL_COUNT_KEY);
        localStorage.removeItem(CHAT_START_KEY);
        localStorage.removeItem(LIVECHAT_NAME_KEY);
        localStorage.removeItem('techno_livechat_session');
        liveChatSessionId = null;
        liveChatVisitorName = null;
        el.messages.innerHTML = '';
        botReply(technoChatbot.welcomeMessage);
        disableInput(false);
    }

    /* ---------- WebSocket Live Chat ---------- */
    function initSocket() {
        if(!technoChatbot.ws_url) return;
        socket = io(technoChatbot.ws_url, { transports: ['websocket'] });
        socket.on("connect", () => {
            console.log("WS connected:", socket.id);
            socket.emit("get-active-sessions");
            liveChatSessionId = localStorage.getItem('techno_livechat_session');
            if(liveChatSessionId){
                socket.emit("visitor-join", { session_id: liveChatSessionId });
            }
        });

        socket.on("receive-message", (msg) => {
            if(msg.session_id !== liveChatSessionId) return;
            if(msg.sender === 'admin') addMessage(msg.message, 'admin');
        });

        socket.on("support-status", (data) => {
            if(typeof data.online === 'boolean'){
                updateStatusDot(data.online);
                console.log(data.online);
            }
        });

        socket.on("disconnect", () => {
            console.log("WS disconnected");
            updateStatusDot(false);
        });
    }
    async function startLiveChat() {
        liveChatSessionId = localStorage.getItem('techno_livechat_session') || ('sess_' + Date.now());
        localStorage.setItem('techno_livechat_session', liveChatSessionId);

        disableInput(false);
        initSocket();

        socket.on("connect", () => {
            socket.emit("visitor-join", { session_id: liveChatSessionId });
        });
        
        /* if(liveChatVisitorName) {
            socket?.emit('visitor-join', { session_id: liveChatSessionId });
        } */
    }

    async function checkAndTransferToLiveChat() {
        if (!technoChatbot.liveChatEnabled) {
            await botReply(technoChatbot.offlineSupport);
            showContactOptions();
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
            statusDotCache = { online, ts: Date.now() };
            updateStatusDot(online);

            if(online){
                setState(6);
                await botReply(technoChatbot.getName);
                disableInput(false);
            } else {
                setState(0);
                await botReply(technoChatbot.offlineSupport);
                showContactOptions();
            }
        } catch(e){
            console.error(e);
            setState(0);
            await botReply(technoChatbot.offlineSupport);
            showContactOptions();
        }
    }

    /* ---------- Chat Send Handler ---------- */
    const handleSend = async () => {
        const userMessage = el.input.value.trim();
        if(!userMessage) return;

        addMessage(userMessage, 'visitor');
        el.input.value = '';
        const state = getState();

        if(state === 2){
            const method = getContactMethod();
            if(method === 'phone'){
                if(parseInt(technoChatbot.timeToCall) === 1){
                    setState(3);
                    await botReply(technoChatbot.timeToCallTxt);
                } else {
                    await finishContact();
                }
            }
            if(method === 'email'){
                await finishContact();
            }
            return;
        }

        if(state === 3){
            await finishContact();
            return;
        }

        if(state === 5 && socket && socket.connected){
            socket.emit("send-message", {
                session_id: liveChatSessionId,
                message: userMessage,
                sender: "visitor"
            });
            return;
        }

        if(state === 6){
            liveChatVisitorName = userMessage;
            localStorage.setItem(LIVECHAT_NAME_KEY, liveChatVisitorName);
            setState(5);
            await botReply(technoChatbot.transferredToSupport);
            await startLiveChat();
            return;
        }

        await handleFaqReply(userMessage);
    };

    /* ---------- Event Listeners ---------- */
    el.send?.addEventListener('click', handleSend);
    el.input?.addEventListener('keypress', (e) => {
        if(e.key === 'Enter') handleSend();
    });

    el.icon.addEventListener('click', () => {
        el.window.classList.toggle('techno-chatbot-hidden');
        if(!el.window.classList.contains('techno-chatbot-hidden')){
            scrollToBottom();
            const history = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            if(!history.length) botReply(technoChatbot.welcomeMessage);
        }
    });
    el.menubtn.addEventListener('click', () => el.menubtn.classList.toggle('active'));
    if(el.reset.length > 0){
        el.reset.forEach(btn => btn.addEventListener('click', () => { clearHistory(); el.menubtn.classList.remove('active'); }));
    }

    /* ---------- Load History ---------- */
    function loadHistory(){
        const history = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        history.forEach(msg => addMessage(msg.text, msg.sender, false));
        const state = getState();
        if(state === 1) showContactOptions(true);
        if(state === 2 || state === 3) disableInput(false);
        if(state === 5){
            initSocket();
            setTimeout(() => {
                if(socket && liveChatSessionId){
                    socket.emit("visitor-join", { session_id: liveChatSessionId });
                }
            }, 500);
        }
        if(state === 6) disableInput(false);

        scrollToBottom();
    }

    initSocket(); 
    loadHistory();
});