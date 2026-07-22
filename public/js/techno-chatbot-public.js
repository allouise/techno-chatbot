class TechnoChatbot {
    constructor() {
        // DOM Elements
        this.el = {};
        
        this.botData = {};
        this.config = null;
        this.storageKeys = null;
        this.inputPlaceholders = null;
        this.optionType = null;

        this.socket = null;
        this.idleDisconnectTimer = null;
        this.conversationId = null;
        this.livechat = false;
        this.supportOnline = false;
        this.requireInput = '';
        this.failedAnswer = 0;
        this.isProcessing = false;
        this.lastSendTime = 0;
        this.minSendInterval = 2000;

        this.init();
    }

    /* ==========================================================================
       Initialization & Setup
       ========================================================================== */

    async init() {
        // localStorage.removeItem(this.storageKeys.session);
        this.cacheElements();

        if (!this.el.icon || !this.el.window || !this.el.messages || !this.el.input || !this.el.send){
            console.warn('TechnoChatbot: Required DOM elements are missing. Class initialization stopped.');
            return false; 
        };

        this.setupConfigurations();
        this.initState();
        this.bindEvents();
        this.initSocket();

        await this.getConversation();

        console.log("Active Conversation ID:", this.conversationId);
    }

    cacheElements() {
        this.el = {
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
            transcriptRequest: document.getElementById('techno-chatbot-transcript-request'),
            statusDot: document.getElementById('techno-support-status-dot'),
        };
    }

    setupConfigurations() {
        this.botData = window.technoChatbot || {};

        this.storageKeys = Object.freeze({
            session: 'techno_chatbot_session',
            livechat: 'techno_livechat_status',
            failedanswer: 'techno_chatbot_fail_count'
        });

        this.inputPlaceholders = Object.freeze({
            waiting: 'Please wait...',
            options: 'Choose an option...',
            default: this.botData.inputtxt || ''
        });

        this.optionType = Object.freeze({
            noAnswer: 'no-answer',
        });

        this.config = Object.freeze({
            failLimit: parseInt(this.botData.noAnswerTrigger) || 0,
            supportIdleTime: parseInt(this.botData.idleTimer) || 0,
            allowed_types: ['phone_input', 'email_input', 'time_input'],
            timeFormatter: new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            }),
            processedFaq: (this.botData.faq || []).map(faq => ({
                ...faq,
                cleanedQuestions: faq.questions.map(question => {
                    const cleaned = this.normalizeText(question);
                    return {
                        original: question,
                        cleaned,
                        words: cleaned.split(' ').filter(word => word.length >= 4)
                    };
                })
            }))
        });
    }

    initState() {
        this.livechat = this.getLiveChatStatus();
        this.failedAnswer = this.getFailCount();
    }

    startIdleDisconnectTimer() {
        if (!this.config.supportIdleTime) return;
        this.clearIdleDisconnectTimer();

        this.idleDisconnectTimer = setTimeout(async () => {
            if (!this.socket?.connected || !this.supportOnline) {
                const existingOptions = this.el.messages?.querySelector('.techno-chatbot-contact-options');
                if (!existingOptions) {
                    if (this.botData.idleSupport) {
                        await this.addMessage(this.botData.idleSupport, 'bot', true, 'system');
                    }
                    this.showOptions(this.optionType.noAnswer);
                }
            }
        }, this.config.supportIdleTime * 1000);
    }

    clearIdleDisconnectTimer() {
        if (this.idleDisconnectTimer) {
            clearTimeout(this.idleDisconnectTimer);
            this.idleDisconnectTimer = null;
        }
    }

    bindEvents() {
        /* Click Submit */
        this.el.send.addEventListener('click', () => this.handleSend());
        this.el.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.handleSend();
            }
        });
        /* Toggle Chatbot Visibility */
        this.el.close?.addEventListener('click', () => this.toggleChatbot());
        this.el.icon?.addEventListener('click', () => this.toggleChatbot());
        /* Toggle Menu */
        this.el.menubtn?.addEventListener('click', (e) => this.toggleMenu(e));
        /* Toggle Disclaimer */
        this.el.disclaimer?.addEventListener('click', () => {
            this.el.disclaimerModal?.classList.add('active');
            this.toggleMenu(false);
        });
        const disclaimerCloseBtn = this.el.disclaimerModal?.querySelector('.close-btn');
        disclaimerCloseBtn?.addEventListener('click', () => {
            this.el.disclaimerModal?.classList.remove('active');
        });
        /* Request Transcript */
        this.el.transcriptRequest?.addEventListener('click', () => {
            // this.requestTranscript();
            this.toggleMenu(false);
        });
        /* Reset */
        this.el.reset?.forEach(btn => btn.addEventListener('click', async () => {
            await this.reset();
            this.toggleMenu(false);
        }));
    }

    /* ==========================================================================
       UI Helpers
       ========================================================================== */

    scrollToBottom() {
        this.el.messages.scrollTop = this.el.messages.scrollHeight;
    }

    showTyping() {
        const typing = document.createElement('div');
        typing.className = 'techno-chatbot-message admin typing';
        typing.innerHTML = `<span></span><span></span><span></span>`;
        this.el.messages.appendChild(typing);
        this.scrollToBottom();
        return typing;
    }

    disableInput(disabled = true) {
        this.el.input.disabled = disabled;
        this.el.send.disabled = disabled;
        if (!disabled) this.focusInput();
    }

    focusInput() {
        this.el.input.focus();
    }

    toggleMenu(open) {
        if (!this.el.menubtn) return;
        if (typeof open === 'boolean') {
            this.el.menubtn.classList.toggle('active', open);
        } else {
            this.el.menubtn.classList.toggle('active');
        }
    }

    toggleChatbot(open) {
        const hidden = typeof open === 'boolean' ? open : !this.el.window.classList.contains('techno-chatbot-hidden');
        this.el.window.classList.toggle('techno-chatbot-hidden', hidden);
        if (!hidden) {
            this.scrollToBottom();
            this.focusInput();
        }
    }

    renderHistory(messages) {
        if (!Array.isArray(messages) || messages.length === 0) return;

        const fragment = document.createDocumentFragment();
        messages.forEach(msg => {
            const messageEl = document.createElement('div');
            messageEl.className = `techno-chatbot-message ${msg.sender}`;

            if (msg.sender === 'visitor') {
                messageEl.textContent = msg.message;
            } else {
                messageEl.innerHTML = msg.message;
            }

            const timeEl = document.createElement('div');
            timeEl.className = 'techno-chatbot-time';
            const dateObj = msg.created_at ? new Date(msg.created_at.replace(/-/g, '/')) : new Date();
            timeEl.textContent = this.config.timeFormatter.format(dateObj);
            messageEl.appendChild(timeEl);
            fragment.appendChild(messageEl);
        });
        this.el.messages.appendChild(fragment);

        let displayedOptions = false;
        const lastMsg = messages[messages.length - 1];
        const type = lastMsg?.message_type;
        if (type === 'system') {
            this.showOptions(this.optionType.noAnswer);
            displayedOptions = true;
        }else if(this.config.allowed_types.includes(type)) {
            this.requireInput = type;
        }

        this.scrollToBottom();
        return displayedOptions;
    }

    showOptions(type = '') {
        if (!type) return;
        this.disableInput(true);
        this.el.input.placeholder = this.inputPlaceholders.options;

        const wrapper = document.createElement('div');
        wrapper.className = 'techno-chatbot-contact-options';
        const options = [];

        if (this.botData.liveChatEnabled && this.supportOnline) {
            options.push({ action: 'livechat', label: this.botData.menuLivechat });
        }

        if (type === this.optionType.noAnswer) {
            options.push(
                { action: 'phone', label: this.botData.menuCall },
                { action: 'email', label: this.botData.menuEmail }
            );
        }

        if (!this.idleDisconnectTimer) {
            options.push({ action: 'restart', label: this.botData.menuReset });
        }

        if (options.length === 0) return;

        const fragment = document.createDocumentFragment();
        options.forEach(({ action, label }) => {
            if (!label) return;
            const btn = document.createElement('button');
            btn.textContent = label;
            btn.dataset.action = action;
            fragment.appendChild(btn);
        });

        wrapper.appendChild(fragment);
        wrapper.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (btn && btn.dataset.action) {
                this.chooseOption(btn.dataset.action);
            }
        });

        this.el.messages.appendChild(wrapper);
        this.scrollToBottom();
    }

    async chooseOption(method) {

        this.el.messages.querySelector('.techno-chatbot-contact-options')?.remove();
        this.disableInput(false);
        this.el.input.placeholder = this.inputPlaceholders.default;

        const inputConfigs = {
            phone: { visitorText: this.botData.menuCall, botLabel: this.botData.cPhoneLabel },
            email: { visitorText: this.botData.menuEmail, botLabel: this.botData.cEmailLabel }
        };

        if (inputConfigs[method]) {
            const { visitorText, botLabel } = inputConfigs[method];
            await this.addMessage(visitorText, 'visitor');
            await this.addMessage(botLabel, 'bot', true, `${method}_input`);
            this.requireInput = `${method}_input`;
            return;
        }

        switch (method) {
            case 'livechat':
                await this.addMessage(this.botData.menuLivechat, 'visitor');
                // await this.checkAndTransferToLiveChat();
                break;

            case 'restart':
                // await this.clearHistory();
                break;
        }
    }

    updateStatusDot(isOnline) {
        if (this.el.statusDot) {
            this.el.statusDot.classList.toggle('online', isOnline);
            this.el.statusDot.classList.toggle('offline', !isOnline);
        }
    }

    /* ==========================================================================
       Utilities & Validation
       ========================================================================== */

    validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    validatePhone(phone) {
        return /^\d{7,15}$/.test(phone.replace(/[\s()+-]/g, ''));
    }

    normalizeText(text = '') {
        return text.toLowerCase().replace(/[^\w\s]/g, '').trim();
    }

    sanitizeText(text = '') {
        return text.replace(/<[^>]+>/g, '').replace(/[\0-\x1F]/g, '').replace(/\s+/g, ' ').trim().slice(0, 1000);
    }

    /* ==========================================================================
       Storage & Local State
       ========================================================================== */

    async reset() {
        this.disableInput(true);
        this.el.input.placeholder = this.inputPlaceholders.waiting;
        this.el.messages.innerHTML = '';
        const typing = this.showTyping();

        try {
            await this.stopConversation();

            localStorage.removeItem(this.storageKeys.session);
            localStorage.removeItem(this.storageKeys.livechat);
            localStorage.removeItem(this.storageKeys.failedanswer);
            
            this.conversationId = null;
            this.livechat = false;
            this.requireInput = '';
            this.failedAnswer = 0;
            this.isProcessing = false;

            await this.getConversation();

        } finally {
            typing?.remove();
            this.disableInput(false);
            this.el.input.placeholder = this.inputPlaceholders.default;
        }
    }

    getSession() {
        return localStorage.getItem(this.storageKeys.session) || null;
    }

    getLiveChatStatus() {
        return localStorage.getItem(this.storageKeys.livechat) === 'true';
    }

    getFailCount() {
        return parseInt(localStorage.getItem(this.storageKeys.failedanswer) || '0');
    }

    increaseFailCount() {
        this.failedAnswer++;
        localStorage.setItem(this.storageKeys.failedanswer, this.failedAnswer);
        return this.failedAnswer;
    }

    resetFailCount() {
        this.failedAnswer = 0;
        localStorage.setItem(this.storageKeys.failedanswer, 0);
        return this.failedAnswer;
    }

    /* ==========================================================================
       Matching Algorithms (FAQ)
       ========================================================================== */

    levenshteinDistance(a, b) {
        const matrix = Array(b.length + 1).fill(null).map(() => Array(a.length + 1).fill(0));
        for (let i = 0; i <= b.length; i++) matrix[i][0] = i;
        for (let j = 0; j <= a.length; j++) matrix[0][j] = j;

        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                matrix[i][j] = b[i - 1] === a[j - 1]
                    ? matrix[i - 1][j - 1]
                    : Math.min(matrix[i - 1][j - 1] + 1, matrix[i][j - 1] + 1, matrix[i - 1][j] + 1);
            }
        }
        return matrix[b.length][a.length];
    }

    stringSimilarity(str1, str2) {
        const longer = str1.length > str2.length ? str1 : str2;
        const shorter = str1.length > str2.length ? str2 : str1;
        if (longer.length === 0) return 1;
        return (longer.length - this.levenshteinDistance(longer, shorter)) / longer.length;
    }

    findFaqAnswer(message) {
        const text = this.normalizeText(message);
        let bestMatch = null;
        let bestScore = 0;

        for (const faq of this.config.processedFaq) {
            let score = 0;
            for (const kw of faq.cleanedQuestions) {
                let matched = false;
                if (text.includes(kw.cleaned)) {
                    score += 5;
                    matched = true;
                }
                for (const word of kw.words) {
                    if (text.includes(word)) {
                        score += 2;
                        matched = true;
                    }
                }
                if (matched) {
                    const similarity = this.stringSimilarity(text, kw.cleaned);
                    if (similarity > 0.8) score += similarity * 5;
                }
            }
            score += faq.priority || 0;
            if (score > bestScore) {
                bestScore = score;
                bestMatch = faq;
            }
        }

        return (bestMatch && bestScore >= 6) ? bestMatch.answer : this.botData.noAnswer;
    }

    /* ==========================================================================
       API / Backend Communication
       ========================================================================== */

    async getConversation() {
        const sessionId = this.getSession();
        if (!sessionId){
            if (this.botData.welcomeMessage) {
                this.addMessage(this.botData.welcomeMessage, 'bot', false);
            }
            return null
        };

        this.disableInput(true);
        this.el.input.placeholder = this.inputPlaceholders.waiting;
        const typing = this.showTyping();
        let hasOptions = false;

        try {
            const formData = new FormData();
            formData.append("action", "techno_get_conversation");
            formData.append("session_id", sessionId);
            formData.append("nonce", this.botData.nonce);

            const res = await fetch(this.botData.ajax_url, {
                method: "POST",
                credentials: "same-origin",
                body: formData
            });

            const data = await res.json();
            if (!data.success) {
                throw new Error("Failed to get conversation");
            }

            this.conversationId = data.data?.id ?? null;
            const messages = data.data?.messages;

            if (Array.isArray(messages) && messages.length > 0) {
                hasOptions = this.renderHistory(messages);
            } else if (this.botData.welcomeMessage) {
                this.addMessage(this.botData.welcomeMessage, 'bot', false);
            }

            return this.conversationId;
        } finally {
            typing?.remove();
            if( hasOptions != true ){
                this.disableInput(false);
                this.el.input.placeholder = this.inputPlaceholders.default;
            }
        }
    }

    async startConversation() {
        if (this.conversationId) return this.conversationId;

        const sessionId = window.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(36).slice(2)}`;
        const formData = new FormData();
        formData.append("action", "techno_new_conversation");
        formData.append("session_id", sessionId);
        formData.append("nonce", this.botData.nonce);
        if ( this.botData.welcomeMessage ) {
            formData.append("message", this.botData.welcomeMessage);
        }

        const res = await fetch(this.botData.ajax_url, {
            method: "POST",
            credentials: "same-origin",
            body: formData
        });
        const data = await res.json();
        if (!data.success) {
            throw new Error("Failed to create conversation");
        }

        this.conversationId = data.data.id;
        localStorage.setItem(this.storageKeys.session, sessionId);

        return this.conversationId;
    }

    async stopConversation() {
        const sessionId = this.getSession();
        if (!this.conversationId || !sessionId) return;

        const formData = new FormData();
        formData.append("action", "techno_end_conversation");
        formData.append("session_id", sessionId);
        formData.append("conversation_id", this.conversationId);
        formData.append("nonce", this.botData.nonce);

        try {
            const res = await fetch(this.botData.ajax_url, {
                method: "POST",
                credentials: "same-origin",
                body: formData
            });
            const data = await res.json();
            if (!data.success) {
                throw new Error(data.data?.message || "Failed to end conversation");
            }

            localStorage.removeItem(this.storageKeys.session);
            this.conversationId = null;

            return true;
        } catch (err) {
            console.error("Error ending conversation:", err);
            return false;
        }
    }

    async findAIAnswer(question) {
        const formData = new FormData();
        formData.append("action", "techno_chatbot_ask_ai");
        formData.append("question", question);
        formData.append("nonce", this.botData.nonce);

        try {
            const res = await fetch(this.botData.ajax_url, {
                method: "POST",
                credentials: "same-origin",
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                return data.data;
            }
            return null;
        } catch (err) {
            console.error(err);
            return null;
        }
    }

    async addMessage(text, sender, save = true, type = 'text', token = null) {
        if( save === true && !this.conversationId ) return;

        const message = document.createElement('div');
        message.className = `techno-chatbot-message ${sender}`;

        if (sender === 'visitor') {
            message.textContent = text;
        } else {
            message.innerHTML = text;
        }

        const time = document.createElement('div');
        time.className = 'techno-chatbot-time';
        time.textContent = this.config.timeFormatter.format(new Date());
        message.appendChild(time);
        this.el.messages.appendChild(message);
        this.scrollToBottom();

        if (!save) return;

        const body = new URLSearchParams({
            action: 'techno_save_chat_message',
            nonce: this.botData.nonce,
            conversation_id: this.conversationId,
            sender,
            message: text,
            message_type: type,
        });
        if (token) {
            body.append('token', JSON.stringify(token));
        }
        const res = await fetch(this.botData.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body,
            keepalive: true
        });
        if (!res.ok) {
            throw new Error(`Failed to save message (${res.status})`);
        }
    }

    async finishInput(){
        if( !this.conversationId ) return;
        await this.addMessage(this.botData.getContactThxMsg, 'bot');

        try {
            await this.stopConversation();
        } catch (err) {
            this.addMessage(this.botData.cerrorMsg, 'bot');
        }
    }

    /* ==========================================================================
       Message Handling Pipeline
       ========================================================================== */

    async handleFaqReply(message) {
        this.disableInput(true);
        this.el.input.placeholder = this.inputPlaceholders.waiting;
        const typing = this.showTyping();
        let options = '';

        try {
            let error = false;
            let answer = null;
            let tokens = null;

            if (this.botData.aiEnabled == 1) {
                const aiResponse = await this.findAIAnswer(message);
                answer = aiResponse?.answer;
                tokens = { 
                    prompt_tokens: aiResponse?.prompt_tokens ?? 0, 
                    completion_tokens: aiResponse?.completion_tokens ?? 0 
                };

                if (answer && answer !== "NO_ANSWER") {
                    this.resetFailCount();
                } else {
                    error = true;
                }
            } else {
                answer = this.findFaqAnswer(message);
                if (answer === this.botData.noAnswer) {
                    error = true;
                } else {
                    this.resetFailCount();
                }
            }

            if (error) {
                this.failedAnswer = this.increaseFailCount();
                if (this.config.failLimit === 0 || this.failedAnswer >= this.config.failLimit) {
                    answer = this.botData.noAnswerFinalDefault || this.botData.noAnswer || '...';
                    options = this.optionType.noAnswer;
                    this.resetFailCount();
                } else {
                    answer = this.botData.noAnswer || '...';
                }
            }

            if( options != '' ){
                await this.addMessage(answer, 'bot', true, 'system', tokens);
                this.showOptions(options);
            }else{
                await this.addMessage(answer, 'bot', true, 'text', tokens);
            }

        } finally {
            typing?.remove();
            if (!options) {
                this.disableInput(false);
                this.el.input.placeholder = this.inputPlaceholders.default;
            }
        }
    }

    async handleSend() {
        const now = Date.now();
        if (now - this.lastSendTime < this.minSendInterval) {
            this.addMessage(this.botData.spamLimitMsg, 'bot', false);
            return; 
        }

        if (this.isProcessing) return;
        this.isProcessing = true;

        try {
            const userInput = this.el.input.value.trim();
            const userMessage = this.sanitizeText(userInput);
            if (!userMessage) return;

            this.lastSendTime = Date.now();
            this.el.input.value = '';

            if (!this.conversationId) {
                await this.startConversation();
            }

            const normalizedText = this.normalizeText(userMessage);
            const matchesKeyword = (keywords) => Array.isArray(keywords) && keywords.some(k => k && normalizedText.includes(this.normalizeText(k)));

            /* Live Chat Trigger */
            if (matchesKeyword(this.botData.transferLiveChatKeywords)) {
                await this.addMessage(userMessage, 'visitor');
                this.resetFailCount();
                // await this.checkAndTransferToLiveChat();
                return;
            }

            /* Next Step / Option Transfer Trigger */
            if (matchesKeyword(this.botData.transferKeywords)) {
                await this.addMessage(userMessage, 'visitor');
                this.resetFailCount();
                
                const replyMsg = this.botData.nextStepMsg || this.botData.noAnswerFinalDefault || this.botData.noAnswer || '...';
                await this.addMessage(replyMsg, 'bot', true, 'system');
                this.showOptions(this.optionType.noAnswer);
                return;
            }

            /* Requires Input */
            if (this.requireInput && this.config.allowed_types?.includes(this.requireInput)) {
                const currentType = this.requireInput;

                if (currentType === 'phone_input') {
                    if (!this.validatePhone(userMessage)) {
                        await this.addMessage(userMessage, 'visitor');
                        await this.addMessage(this.botData.phoneError, 'bot', true, currentType);
                        return;
                    }

                    await this.addMessage(userMessage, 'visitor', true, `${currentType}_answer`);
                    
                    if (parseInt(this.botData.timeToCall, 10) === 1) {
                        await this.addMessage(this.botData.timeToCallTxt, 'bot', true, 'time_input');
                        this.requireInput = 'time_input';
                    } else {
                        await this.finishInput();
                        this.requireInput = '';
                    }
                    return;
                }

                if (currentType === 'email_input') {
                    if (!this.validateEmail(userMessage)) {
                        await this.addMessage(userMessage, 'visitor');
                        await this.addMessage(this.botData.emailError, 'bot', true, currentType);
                        return;
                    }

                    await this.addMessage(userMessage, 'visitor', true, `${currentType}_answer`);
                    await this.finishInput();
                    this.requireInput = '';
                    return;
                }

                if (currentType === 'time_input') {
                    await this.addMessage(userMessage, 'visitor', true, `${currentType}_answer`);
                    await this.finishInput();
                    this.requireInput = '';
                    return;
                }
            }
            
            /* Usual Add Message */
            await this.addMessage(userMessage, 'visitor');
            
            /* FAQ/AI Answers */
            await this.handleFaqReply(userMessage);

        } finally {
            this.isProcessing = false;
        }
    }
    /* ==========================================================================
       WebSocket / Realtime Communication
       ========================================================================== */

    initSocket() {
        const wsUrl = this.botData.ws_url;
        if (!wsUrl || this.socket) return;
        if (typeof io !== 'function') {
            console.warn("Socket.io library not loaded.");
            return;
        }

        this.socket = io(wsUrl, {
            transports: ['polling', 'websocket'],
            secure: true,
            reconnection: true,
            reconnectionAttempts: Infinity,
            reconnectionDelay: 1000,
            reconnectionDelayMax: 5000,
            timeout: 20000,
            auth: {
                site: this.botData.site_id,
                token: this.botData.token
            }
        });

        /* ---- Event Listeners ---- */
        this.socket.on("connect", () => {
            this.clearIdleDisconnectTimer();
            /* const liveChatSessionId = this.getSession();
            if (liveChatSessionId) {
                this.checkArchived(liveChatSessionId);
            } */
        });

        this.socket.on("connect_error", async () => {
            const options = !!this.el.messages.querySelector('.techno-chatbot-contact-options');
            if (!options && this.conversationId) {
                if (this.botData.idleSupport) {
                    await this.addMessage(this.botData.idleSupport, 'bot', true, 'system');
                }
                this.showNoAnswerOptions();
            }
        });

        this.socket.on("receive-message", async (msg) => {
            const currentSession = this.getSession();
            if (!msg || msg.session_id !== currentSession) return;

            if (msg.sender === 'admin') {
                const cleanMessage = (msg.message || '').trim();

                if (['/endchat', '/endchat1'].includes(cleanMessage)) {
                    await this.handleEndChatCommand(cleanMessage);
                    return;
                }

                // Render live message from support agent (don't re-save via AJAX)
                this.addMessage(msg.message, 'admin', false);
            }
        });

        this.socket.on("support-status", (data) => {
            if (typeof data?.online === 'boolean') {
                this.updateStatusDot(data.online);

                if (data.online) {
                    this.clearIdleDisconnectTimer();
                } else {
                    this.startIdleDisconnectTimer();
                }

                this.supportOnline = data.online;
                
                const existingOptions = this.el.messages.querySelector('.techno-chatbot-contact-options');
                if (existingOptions) {
                    existingOptions.remove();  
                    this.showOptions(this.optionType.noAnswer); 
                }
            }
        });

        this.socket.on("unregister-support", () => {
            this.updateStatusDot(false);
            this.startIdleDisconnectTimer();
        });

        this.socket.on("disconnect", () => {
            this.updateStatusDot(false);
            this.startIdleDisconnectTimer();
        });
    }
}

/* Instantiation on DOM Ready */
document.addEventListener('DOMContentLoaded', () => {
    window.technoChatbotInstance = new TechnoChatbot();
});