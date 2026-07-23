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
        this.sessionId = null;
        this.socketId = null;
        this.socketName = null;
        this.recentSession = null;
        this.supportOnline = false;
        this.requireInput = '';
        this.state = '';
        this.failedAnswer = 0;
        this.isProcessing = false;
        this.lastSendTime = 0;
        this.minSendInterval = 2000;
        this.hasHandledConnectError = false;

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

        console.log("Active Conversation & Session ID:", this.conversationId + ' ' + this.sessionId);
        console.log('socketId & socketName:', this.socketId + ' ' + this.socketName);
        console.log('state', this.state);
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
            state: 'techno_chatbot_state',
            session: 'techno_chatbot_session',
            failedanswer: 'techno_chatbot_fail_count',
            recentsession: 'techno_chatbot_recent_session'
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
            liveChatEnabled: Boolean(this.botData.liveChatEnabled),
            allowed_types: ['phone_input', 'email_input', 'time_input', 'name_input'],
            allowed_states: ['request_transcript'],
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
        this.failedAnswer = this.getFailCount();
        this.sessionId = this.getSession();
        this.recentSession = this.getRecentSession();
        this.state = this.getState();
        this.updateTranscriptButtonVisibility();
    }

    async socketIdleCheck(socket_error = false) {
        if(!this.config.liveChatEnabled) return;
        
        const idleTime = this.config.supportIdleTime;
        this.clearIdleDisconnectTimer();
        
        /* Update existing options on support status change */
        let existingOptions = this.el.messages.querySelector('.techno-chatbot-contact-options');
        if (existingOptions) {
            existingOptions.remove();
            this.showOptions(this.optionType.noAnswer);
            existingOptions = this.el.messages.querySelector('.techno-chatbot-contact-options');
        }

        /* If socket id or currently livechatting and socket got disconnected due to error */
        if (socket_error && this.socketId != null) {
            if (!existingOptions) {
                await this.addMessage(this.botData.idleSupport, 'bot', true, 'system');
                this.showOptions(this.optionType.noAnswer);
            }
            return;
        }

        /* If idleTime is setup and socket id or currently livechatting and support got disconnected */
        if (idleTime > 0 && this.socketId != null && !this.supportOnline) {
            this.idleDisconnectTimer = setTimeout(async () => {
                const currentOptions = this.el.messages.querySelector('.techno-chatbot-contact-options');
                if (!currentOptions) {
                    await this.addMessage(this.botData.idleSupport, 'bot', true, 'system');
                    this.showOptions(this.optionType.noAnswer);
                }
            }, idleTime * 1000);
        }
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
            this.initRequestTranscript();
            this.toggleMenu(false);
        });
        /* Reset */
        this.el.reset?.forEach(btn => btn.addEventListener('click', async () => {
            await this.addMessage(this.botData.menuReset, 'visitor');
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

    updateTranscriptButtonVisibility() {
        if (!this.el.transcriptRequest) return;        
        const shouldHide = this.state === 'request_transcript' || this.recentSession == null || this.socketId != null;
        this.el.transcriptRequest.classList.toggle('techno-cb-hide', shouldHide);
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

    updateStatusDot(isOnline) {
        if (this.el.statusDot) {
            this.el.statusDot.classList.toggle('online', isOnline);
            this.el.statusDot.classList.toggle('offline', !isOnline);
        }
    }

    async initRequestTranscript() {
        this.disableInput(false);
        this.el.input.placeholder = this.inputPlaceholders.default;
        this.setState('request_transcript');
        this.requireInput = 'email_input';
        await this.addMessage(this.botData.menuHistorySend, 'visitor',);
        await this.addMessage(this.botData.askEmail, 'bot', true, 'email_input');
    }

    showOptions(type = '') {
        if (!type) return;
        this.disableInput(true);
        this.el.input.placeholder = this.inputPlaceholders.options;

        const wrapper = document.createElement('div');
        wrapper.className = 'techno-chatbot-contact-options';
        const options = [];

        if (this.config.liveChatEnabled && this.supportOnline) {
            options.push({ action: 'livechat', label: this.botData.menuLivechat });
        }

        if (type === this.optionType.noAnswer) {
            options.push(
                { action: 'phone', label: this.botData.menuCall },
                { action: 'email', label: this.botData.menuEmail },
                { action: 'restart', label: this.botData.menuReset }
            );
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
                await this.transferLiveChat();
            break;

            case 'restart':
                await this.addMessage(this.botData.menuReset, 'visitor');
                await this.reset();
            break;
        }
    }

    async finishInput(){
        if( this.conversationId == null ) return;

        if( this.state == 'request_transcript' ){
            await this.requestTranscript();
            return;
        }

        try {
            await this.addMessage(this.botData.getContactThxMsg, 'bot');
            await this.stopConversation();
        } catch (err) {
            console.error("Error ending conversation:", err);
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

    async transferLiveChat(name = '') {
        const isLive = this.config.liveChatEnabled && this.supportOnline;

        if (!isLive) {
            await this.addMessage(this.botData.offlineSupport, 'bot', true, 'system');
            this.showOptions(this.optionType.noAnswer);
            return;
        }

        const requiresName = Number(this.botData.liveChatGetName) === 1;

        /* If user is new and a name is required, but none was provided yet */
        if (!this.socketId && requiresName && !name.trim()) {
            await this.addMessage(this.botData.getName, 'bot', true, 'name_input');
            this.requireInput = `name_input`;
            return;
        }

        /* If user is new (and has either provided a name or name isn't required) */
        if (!this.socketId) {
            const makeId = () => Math.random().toString(36).substring(2, 6).padEnd(4, '0');
            this.socketId = `sess_${Date.now()}_${makeId()}`;
            const visitorName = name.trim() !== '' ? name.trim() : this.socketId;
            await this.addMessage(this.botData.transferredToSupport, 'bot', true);
            this.socket.emit("visitor-join", { 
                session_id: this.socketId, 
                visitor_name: visitorName 
            });
            this.updateConversation(visitorName);
        }else if(this.socketId){
            await this.addMessage(this.botData.transferredToSupport, 'bot', true);
            this.socket.emit("visitor-join", { 
                session_id: this.socketId, 
                visitor_name: this.socketName 
            });
        }
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
            localStorage.removeItem(this.storageKeys.failedanswer);
            localStorage.removeItem(this.storageKeys.state);
            
            this.conversationId = null;
            this.socketId = null;
            this.socketName = null;
            this.requireInput = '';
            this.state = '';
            this.failedAnswer = 0;
            this.isProcessing = false;

            await this.getConversation();

        } finally {
            typing?.remove();
            this.disableInput(false);
            this.el.input.placeholder = this.inputPlaceholders.default;
        }
    }

    getState(){
        return localStorage.getItem(this.storageKeys.state) || null;
    }

    setState(state){
        if(this.config.allowed_states.includes(state)){
            this.state = state;
            localStorage.setItem(this.storageKeys.state, state);
        }
    }

    getSession() {
        return localStorage.getItem(this.storageKeys.session) || null;
    }

    getRecentSession() {
        return localStorage.getItem(this.storageKeys.recentsession) || null;
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

    async apiCall(action, params = {}, { urlEncoded = false, keepalive = false } = {}) {
        const nonce = this.botData.nonce || '';
        let body;
        const fetchOptions = {
            method: "POST",
            credentials: "same-origin"
        };

        if (urlEncoded) {
            body = new URLSearchParams({ action, nonce, ...params });
            fetchOptions.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
        } else {
            body = new FormData();
            body.append("action", action);
            body.append("nonce", nonce);
            for (const [key, value] of Object.entries(params)) {
                body.append(key, value);
            }
        }

        fetchOptions.body = body;
        if (keepalive) fetchOptions.keepalive = true;

        return fetch(this.botData.ajax_url, fetchOptions);
    }

    async getConversation() {
        if (this.sessionId == null){
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
            const res = await this.apiCall("techno_get_conversation", {
                session_id: this.sessionId
            });

            const data = await res.json();
            if (!data.success) {
                throw new Error("Failed to get conversation");
            }

            this.conversationId = data.data?.conversation ?? null;
            this.socketId = data.data?.socket ?? null;
            this.socketName = data.data?.visitor_name ?? this.socketId;

            this.updateTranscriptButtonVisibility();

            const messages = data.data?.messages;
            this.socket.emit("visitor-join", { 
                session_id: this.socketId, 
                visitor_name: this.socketName
            });

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
        if (this.conversationId != null) return this.conversationId;

        const sessionId = window.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(36).slice(2)}`;
        const params = { session_id: sessionId };
        if ( this.botData.welcomeMessage ) {
            params.message = this.botData.welcomeMessage;
        }

        const res = await this.apiCall("techno_new_conversation", params);
        const data = await res.json();
        if (!data.success) {
            throw new Error("Failed to create conversation");
        }

        this.conversationId = data.data.id;
        this.sessionId = sessionId;
        this.recentSession = sessionId;
        localStorage.setItem(this.storageKeys.session, sessionId);
        localStorage.setItem(this.storageKeys.recentsession, sessionId);
        this.updateTranscriptButtonVisibility();

        return this.conversationId;
    }

    async updateConversation(name = '') {
        if (this.conversationId == null || this.sessionId == null || this.socketId == null ) {
            console.warn("Cannot update conversation: Missing required session data.");
            return false;
        }

        try {
            const res = await this.apiCall("techno_update_conversation", {
                session_id: String(this.sessionId).trim(),
                conversation_id: String(this.conversationId).trim(),
                socket_id: String(this.socketId).trim(),
                name: typeof name === 'string' ? name.trim() : ''
            });
            if (!res.ok) {
                const errorText = await res.text();
                throw new Error(`Server returned status ${res.status}: ${errorText}`);
            }
            const data = await res.json();
            if (!data.success) {
                throw new Error(data.data?.message || "Failed to update conversation");
            }
            return true;
        } catch (err) {
            console.error("Error updating conversation:", err);
            return false;
        }
    }

    async stopConversation() {
        if (this.conversationId == null || this.sessionId == null) return;

        try {
            const res = await this.apiCall("techno_end_conversation", {
                session_id: this.sessionId,
                conversation_id: this.conversationId
            });
            const data = await res.json();
            if (!data.success) {
                throw new Error(data.data?.message || "Failed to end conversation");
            }

            if( this.socketId != null ){
                this.socket.emit("end-chat", { session_id: this.socketId });
            }
            localStorage.removeItem(this.storageKeys.session);
            localStorage.removeItem(this.storageKeys.failedanswer);
            this.failedAnswer = 0;
            this.conversationId = null;
            this.sessionId = null;
            this.socketId = null;
            this.socketName = null;

            return true;
        } catch (err) {
            console.error("Error ending conversation:", err);
            return false;
        }
    }

    async findAIAnswer(question) {
        try {
            const res = await this.apiCall("techno_chatbot_ask_ai", { question });
            const data = await res.json();
            if (data.success) {
                return data.data;
            }
            return null;
        } catch (err) {
            console.error("Error finding AI answer:", err);
            return null;
        }
    }

    async addMessage(text, sender, save = true, type = 'text', token = null) {
        if( save === true && ( this.conversationId == null || this.sessionId == null ) ) return;

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

        const params = {
            conversation_id: this.conversationId,
            session_id: this.sessionId,
            sender,
            message: text,
            message_type: type,
        };
        if (token) {
            params.token = JSON.stringify(token);
        }

        const res = await this.apiCall("techno_save_chat_message", params, { urlEncoded: true, keepalive: true });
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            if( data.data.message ){
                this.addMessage(data.data.message, 'bot', false);
            }
            throw new Error(`Failed to save message (${res.status})`);
        }
    }

    async requestTranscript() {
        if (this.recentSession == null) return;

        let typing = null;
        try {
            this.disableInput(true);
            this.el.input.placeholder = this.inputPlaceholders.waiting;
            typing = this.showTyping();

            /* destory the state early */
            localStorage.removeItem(this.storageKeys.state);
            this.state = '';

            const res = await this.apiCall("techno_chatbot_request_transcript", {
                session_id: this.recentSession
            });

            if (!res.ok) {
                throw new Error(`Server returned status ${res.status}`);
            }

            const data = await res.json();
            typing?.remove();
            typing = null;
            if (data.success) {
                await this.addMessage(this.botData.historySent, 'bot', true);
            } else {
                await this.addMessage(this.botData.errorMsg, 'bot', false);
            }

        } catch (err) {
            console.error("Error requesting transcript:", err);
            typing?.remove();
            await this.addMessage(this.botData.errorMsg, 'bot', false);
        } finally {
            this.disableInput(false);
            this.el.input.placeholder = this.inputPlaceholders.default;
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

            typing?.remove();

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

            if (this.conversationId == null) {
                await this.startConversation();
            }

            if( this.botData.transferLiveChatKeywords.length > 0 || this.botData.transferKeywords.length > 0 ){
                const normalizedText = this.normalizeText(userMessage);
                const matchesKeyword = (keywords) => Array.isArray(keywords) && keywords.some(k => k && normalizedText.includes(this.normalizeText(k)));

                /* Live Chat Trigger */
                if (matchesKeyword(this.botData.transferLiveChatKeywords)) {
                    await this.addMessage(userMessage, 'visitor');
                    this.resetFailCount();
                    await this.transferLiveChat();
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

                if (currentType === 'name_input') {
                    await this.addMessage(userMessage, 'visitor', true, `${currentType}_answer`);
                    await this.transferLiveChat(userMessage);
                    this.requireInput = '';
                    return;
                }
            }

            /* Usual Add Message */
            await this.addMessage(userMessage, 'visitor');

            /* Transferred to Live Chat Message */
            if(this.socket && this.socket.connected && this.socketId != null){
                this.socket.emit("send-message", {
                    session_id: this.socketId,
                    message: userMessage,
                    sender: "visitor"
                });
                return;
            }else{
                /* FAQ/AI Answers */
                await this.handleFaqReply(userMessage);
            }

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
            this.hasHandledConnectError = false;
            this.clearIdleDisconnectTimer();
        });

        this.socket.on("connect_error", () => {
            if (!this.hasHandledConnectError) {
                this.hasHandledConnectError = true;       
                this.supportOnline = false;
                this.socketIdleCheck(true);
            }
        });

        this.socket.on("receive-message", (msg) => {
            // const currentSession = this.getSession();
            if (!msg || msg.session_id !== this.sessionId) return;

            if (msg.sender === 'admin') {
                const cleanMessage = (msg.message || '').trim();

                // if (['/endchat', '/endchat1'].includes(cleanMessage)) {
                //     await this.handleEndChatCommand(cleanMessage);
                //     return;
                // }

                // Render live message from support agent (don't re-save via AJAX)
                this.addMessage(msg.message, 'admin', false);
            }
        });

        this.socket.on("support-status", (data) => {
            if (typeof data?.online === 'boolean') {
                this.supportOnline = data.online;
                this.updateStatusDot(data.online);
                this.socketIdleCheck();
            }
        });

        this.socket.on("unregister-support", () => {
            this.supportOnline = false;
            this.updateStatusDot(false);
            this.socketIdleCheck();
        });

        this.socket.on("disconnect", () => {
            this.supportOnline = false;
            this.updateStatusDot(false);
            this.socketIdleCheck();
        });
    }
}

/* Instantiation on DOM Ready */
document.addEventListener('DOMContentLoaded', () => {
    window.technoChatbotInstance = new TechnoChatbot();
});