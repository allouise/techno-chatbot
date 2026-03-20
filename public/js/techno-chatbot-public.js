/**
 * Techno Chatbot Public Script
 */

document.addEventListener('DOMContentLoaded', () => {

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

    const STORAGE_KEY = 'techno_chatbot_history';
    const FAIL_COUNT_KEY = 'techno_chatbot_fail_count';
    const CONTACT_STATE_KEY = 'techno_chatbot_contact_state';
    const CONTACT_METHOD_KEY = 'techno_chatbot_contact_method';
    const CHAT_START_KEY = 'techno_chatbot_start';

    if(!localStorage.getItem(CHAT_START_KEY)){
        localStorage.setItem(CHAT_START_KEY, Date.now());
    }

    /* Helpers */
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
        return text
            .toLowerCase()
            .replace(/[^\w\s]/g,'')
            .trim();
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
        const perChar = 0;/* 30 */
        const max = 3000;
        return Math.min(base + text.length * perChar, max);
    }

    function loadHistory() {
        const history = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        history.forEach(msg => addMessage(msg.text, msg.sender, false));
        const state = getState();

        if(state === 1){
            showContactOptions(true);
        }
        if(state === 2 || state === 3){
            disableInput(false);
        }
        scrollToBottom();
    }

    function clearHistory() {
        localStorage.removeItem(STORAGE_KEY);
        localStorage.removeItem(CONTACT_STATE_KEY);
        localStorage.removeItem(CONTACT_METHOD_KEY);
        localStorage.removeItem(FAIL_COUNT_KEY);
        localStorage.removeItem(CHAT_START_KEY);
        el.messages.innerHTML = '';
        botReply(technoChatbot.welcomeMessage);
        disableInput(false);
    }

    function disableInput(_switch = true){
        if( _switch == false ){
            el.input.disabled = false;
            el.send.disabled = false;
        }else{
            el.input.disabled = true;
            el.send.disabled = true;
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
                if(text.includes(cleanKeyword)){
                    score += 5;
                }
                const words = cleanKeyword.split(' ');
                words.forEach(word => {
                    /* ignore short/common words */
                    if(word.length < 4) return;
                    if(text.includes(word)){
                        score += 2;
                    }
                });
                const similarity = stringSimilarity(text, cleanKeyword);
                if(similarity > 0.8){
                    score += similarity * 5;
                }
            }
            score += (faq.priority || 0);
            if(score > bestScore){
                bestScore = score;
                bestMatch = faq;
            }
        }
        /* Match threshold */
        if(bestMatch && bestScore >= 6){
            return bestMatch.answer;
        }

        return technoChatbot.noAnswer;
    }

    function stringSimilarity(str1, str2){
        const longer = str1.length > str2.length ? str1 : str2;
        const shorter = str1.length > str2.length ? str2 : str1;
        const longerLength = longer.length;
        if(longerLength === 0) return 1;

        const distance = levenshteinDistance(longer, shorter);
        return (longerLength - distance) / longerLength;
    }

    function levenshteinDistance(a, b){
        const matrix = [];
        for(let i = 0; i <= b.length; i++){
            matrix[i] = [i];
        }
        for(let j = 0; j <= a.length; j++){
            matrix[0][j] = j;
        }
        for(let i = 1; i <= b.length; i++){
            for(let j = 1; j <= a.length; j++){

                if(b.charAt(i-1) === a.charAt(j-1)){
                    matrix[i][j] = matrix[i-1][j-1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i-1][j-1] + 1,
                        matrix[i][j-1] + 1,
                        matrix[i-1][j] + 1
                    );
                }

            }
        }
        return matrix[b.length][a.length];
    }

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
                await botReply(technoChatbot.noAnswerFinal);
                const nextStep = parseInt(technoChatbot.nextStep);
                if (nextStep === 0) {
                    showContactOptions(); 
                } else if (nextStep === 1) {
                    /* do nothing — just show noAnswerFinal, no further action */
                } else if (nextStep === 2) {
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

    async function checkAndTransferToLiveChat() {
        
        const res = await fetch(technoChatbot.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'techno_check_support_online',
                nonce: technoChatbot.nonce
            })
        });
        const data = await res.json();

        if (data.success && data.data.online) {
            // Support is online — initiate live chat
            setState(5); // new state: live chat mode
            await botReply(technoChatbot.liveChatConnectingMsg);
            startLiveChatPolling();
        } else {
            // Nobody online — fall back to contact options or message
            await botReply(technoChatbot.liveChatOfflineMsg);
            showContactOptions(); // or just leave it, your choice
        }
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

        if(!restored){
            setState(1);
        }

        phoneBtn.onclick = () => chooseContact('phone');
        emailBtn.onclick = () => chooseContact('email');
    }

    function chooseContact(method){
        setContactMethod(method);
        setState(2);

        const options = document.querySelector('.techno-chatbot-contact-options');
        if(options) options.remove();

        disableInput(false);
        
        let methodLabel = technoChatbot.cEmailLabel;
        switch (method) {
            case 'phone':
                methodLabel = technoChatbot.cPhoneLabel;
            break;
        }

        addMessage(methodLabel, 'admin', true);
    }

    function finishContact(){
        setState(4);
        botReply(technoChatbot.getContactThxMsg);
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
        .then(async res => {
            if (!res.ok) {
                throw new Error(`Server error: ${res.status}`);
            }
            const data = await res.json();
            return data;
        })
        .then(data => {
            if (data.success) {
                clearHistory();
            } else {
                botReply(technoChatbot.cerrorMsg);
            }
        })
        .catch(err => {
            console.error(err);
            botReply(technoChatbot.errorMsg);
        });
    }

    /* Load chat history */
    loadHistory();

    /* Chatbot interactions */
    el.icon.addEventListener('click', () => {
        el.window.classList.toggle('techno-chatbot-hidden');
        const history = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        if (!history.length) {
            botReply(technoChatbot.welcomeMessage);
        }

        if (!el.window.classList.contains('techno-chatbot-hidden')) {
            scrollToBottom();
        }
    });

    el.close?.addEventListener('click', () => {
        el.window.classList.add('techno-chatbot-hidden');
    });

    el.menubtn.addEventListener('click', () => {
        el.menubtn.classList.toggle('active');
    });

    if( el.reset.length > 0 ){
        el.reset.forEach(btn => {
            btn.addEventListener('click', () => { clearHistory(); el.menubtn.classList.toggle('active'); });
        });
    }

    const handleSend = async () => {
        const userMessage = el.input.value.trim();
        if (!userMessage) return;

        addMessage(userMessage, 'visitor');
        el.input.value = '';
        const state = getState();

        if(state === 2){
            const method = getContactMethod();
            if(method === 'phone'){
                if(parseInt(technoChatbot.timeToCall) === 1){
                    setState(3);
                    await botReply(technoChatbot.timeToCallTxt);
                }else{
                    finishContact();
                }
            }

            if(method === 'email'){
                finishContact();
            }
            return;
        }

        if(state === 3){
            await finishContact();
            return;
        }
        await handleFaqReply(userMessage);
    };

    el.send?.addEventListener('click', handleSend);
    el.input?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleSend();
    });

});