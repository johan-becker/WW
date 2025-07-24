// Modern Werewolf Game - In-Game JavaScript
class WerewolfGame {
    constructor() {
        this.gameState = {
            phase: 'night', // night, day, voting, discussion
            timeRemaining: 165, // seconds
            players: [],
            currentPlayer: null,
            selectedTarget: null,
            chatTabs: ['all', 'werewolf', 'dead'],
            currentChatTab: 'all',
            gameCode: 'ABC123',
            isHost: false
        };

        this.sounds = {
            notification: null,
            phaseChange: null,
            action: null
        };

        this.notifications = [];
        this.chatMessages = [];
        
        this.init();
    }

    init() {
        this.setupGame();
        this.setupEventListeners();
        this.setupChat();
        this.startGameTimer();
        this.loadPlayers();
        this.setupSounds();
    }

    setupGame() {
        // Initialize current player (this would come from server)
        this.gameState.currentPlayer = {
            id: 'player1',
            name: 'You',
            role: 'werewolf',
            alive: true,
            avatar: 'YU'
        };

        // Update HUD with current role
        this.updateRoleDisplay();
        this.updatePhaseDisplay();
    }

    setupEventListeners() {
        // Chat tabs
        document.querySelectorAll('.chat-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                this.switchChatTab(e.target.dataset.tab);
            });
        });

        // Chat input
        const chatInput = document.getElementById('chatInput');
        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.sendMessage();
                }
            });
        }

        // Player selection
        document.addEventListener('click', (e) => {
            if (e.target.closest('.player-card:not(.dead)')) {
                this.selectPlayer(e.target.closest('.player-card'));
            }
        });

        // Confirm action button
        const confirmBtn = document.getElementById('confirmAction');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                this.confirmAction();
            });
        }

        // Mobile responsiveness
        this.setupMobileHandlers();
    }

    setupMobileHandlers() {
        // Mobile action bar buttons
        window.toggleRoleSidebar = () => this.togglePanel('roleSidebar');
        window.toggleGameLog = () => this.togglePanel('gameLogPanel');
        window.toggleChat = () => this.toggleMobileChat();
        window.toggleSettings = () => this.togglePanel('settingsPanel');
        
        // Global functions for HTML handlers
        window.sendMessage = () => this.sendMessage();
        window.cancelAction = () => this.cancelAction();
        window.confirmLeaveGame = () => this.confirmLeaveGame();
    }

    // Game State Management
    updatePhaseDisplay() {
        const phaseIndicator = document.querySelector('.phase-indicator');
        const phaseText = document.querySelector('.phase-text');
        
        if (phaseIndicator && phaseText) {
            phaseIndicator.className = `phase-indicator ${this.gameState.phase}`;
            
            const phaseNames = {
                night: '🌙 Night Phase',
                day: '☀️ Day Phase',
                voting: '🗳️ Voting Phase',
                discussion: '💬 Discussion'
            };
            
            phaseText.textContent = phaseNames[this.gameState.phase] || 'Unknown Phase';
        }

        // Update action card based on phase
        this.updateActionCard();
    }

    updateActionCard() {
        const actionCard = document.querySelector('.action-card');
        const actionTitle = document.querySelector('.action-title');
        const actionDescription = document.querySelector('.action-description');
        const confirmButton = document.getElementById('confirmAction');

        if (!actionCard) return;

        const role = this.gameState.currentPlayer.role;
        const phase = this.gameState.phase;

        let title, description, buttonText, buttonClass;

        if (phase === 'night' && role === 'werewolf') {
            title = '🌙 Choose Your Target';
            description = 'Select a player to eliminate during the night.';
            buttonText = '🎯 Confirm Kill';
            buttonClass = 'btn-danger';
        } else if (phase === 'day' && role === 'seer') {
            title = '🔮 Use Your Vision';
            description = 'Select a player to learn their true role.';
            buttonText = '👁️ Investigate';
            buttonClass = 'btn-primary';
        } else if (phase === 'voting') {
            title = '🗳️ Cast Your Vote';
            description = 'Vote to eliminate a suspected werewolf.';
            buttonText = '✅ Vote';
            buttonClass = 'btn-warning';
        } else {
            title = '⏳ Waiting...';
            description = 'Wait for your turn or the next phase.';
            buttonText = '⏳ Wait';
            buttonClass = 'btn-ghost';
        }

        if (actionTitle) actionTitle.textContent = title;
        if (actionDescription) actionDescription.textContent = description;
        if (confirmButton) {
            confirmButton.textContent = buttonText;
            confirmButton.className = `btn btn-lg ${buttonClass}`;
        }
    }

    updateRoleDisplay() {
        const roleInfo = document.querySelector('.role-info');
        const roleIcon = roleInfo?.querySelector('.role-icon');
        const roleName = roleInfo?.querySelector('.role-name');

        if (roleInfo && roleIcon && roleName) {
            const role = this.gameState.currentPlayer.role;
            
            roleInfo.className = `role-info ${role}`;
            
            const roleData = {
                werewolf: { icon: '🐺', name: 'Werewolf' },
                villager: { icon: '👨‍🌾', name: 'Villager' },
                seer: { icon: '🔮', name: 'Seer' },
                hunter: { icon: '🏹', name: 'Hunter' },
                witch: { icon: '🧙‍♀️', name: 'Witch' }
            };

            const data = roleData[role] || roleData.villager;
            roleIcon.textContent = data.icon;
            roleName.textContent = data.name;
        }

        // Update teammates if werewolf
        if (this.gameState.currentPlayer.role === 'werewolf') {
            this.updateTeammates();
        }
    }

    updateTeammates() {
        const teammatesList = document.querySelector('.teammate-list');
        if (!teammatesList) return;

        // Simulate werewolf teammates
        const teammates = [
            { id: 'player3', name: 'AlphaWolf', avatar: 'AW' },
            { id: 'player7', name: 'NightHunter', avatar: 'NH' }
        ];

        teammatesList.innerHTML = '';
        teammates.forEach(teammate => {
            const teammateEl = document.createElement('div');
            teammateEl.className = 'teammate';
            teammateEl.innerHTML = `
                <div class="teammate-avatar">${teammate.avatar}</div>
                <span>${teammate.name}</span>
            `;
            teammatesList.appendChild(teammateEl);
        });
    }

    // Timer Management
    startGameTimer() {
        this.updateTimer();
        this.timerInterval = setInterval(() => {
            this.gameState.timeRemaining--;
            this.updateTimer();
            
            if (this.gameState.timeRemaining <= 0) {
                this.handlePhaseEnd();
            }
        }, 1000);
    }

    updateTimer() {
        const timerEl = document.getElementById('gameTimer');
        if (timerEl) {
            const minutes = Math.floor(this.gameState.timeRemaining / 60);
            const seconds = this.gameState.timeRemaining % 60;
            timerEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Add urgency styling
            if (this.gameState.timeRemaining <= 30) {
                timerEl.style.color = 'var(--accent-danger)';
                timerEl.style.animation = 'pulse 1s infinite';
            } else {
                timerEl.style.color = 'var(--accent-primary)';
                timerEl.style.animation = '';
            }
        }
    }

    handlePhaseEnd() {
        clearInterval(this.timerInterval);
        
        // Simulate phase transition
        this.showNotification('Phase ended! Transitioning...', 'info');
        
        setTimeout(() => {
            this.transitionPhase();
        }, 2000);
    }

    transitionPhase() {
        const phases = ['night', 'day', 'voting', 'discussion'];
        const currentIndex = phases.indexOf(this.gameState.phase);
        const nextPhase = phases[(currentIndex + 1) % phases.length];
        
        this.gameState.phase = nextPhase;
        this.gameState.timeRemaining = this.getPhaseTime(nextPhase);
        this.gameState.selectedTarget = null;
        
        this.updatePhaseDisplay();
        this.startGameTimer();
        this.clearPlayerSelection();
        
        this.showNotification(`${nextPhase.charAt(0).toUpperCase() + nextPhase.slice(1)} phase started!`, 'success');
        this.playSoundEffect('phaseChange');
    }

    getPhaseTime(phase) {
        const phaseTimes = {
            night: 180,    // 3 minutes
            day: 300,      // 5 minutes
            voting: 120,   // 2 minutes
            discussion: 240 // 4 minutes
        };
        return phaseTimes[phase] || 180;
    }

    // Player Management
    loadPlayers() {
        // Simulate player data
        this.gameState.players = [
            { id: 'player1', name: 'You', role: 'werewolf', alive: true, avatar: 'YU' },
            { id: 'player2', name: 'Alice', role: 'villager', alive: true, avatar: 'AL' },
            { id: 'player3', name: 'AlphaWolf', role: 'werewolf', alive: true, avatar: 'AW' },
            { id: 'player4', name: 'Bob', role: 'villager', alive: true, avatar: 'BO' },
            { id: 'player5', name: 'Carol', role: 'seer', alive: false, avatar: 'CA' },
            { id: 'player6', name: 'Dave', role: 'villager', alive: true, avatar: 'DA' },
            { id: 'player7', name: 'NightHunter', role: 'werewolf', alive: true, avatar: 'NH' },
            { id: 'player8', name: 'Eve', role: 'hunter', alive: true, avatar: 'EV' }
        ];

        this.renderPlayers();
        this.updatePlayersAlive();
    }

    renderPlayers() {
        const playersGrid = document.getElementById('playersGrid');
        if (!playersGrid) return;

        playersGrid.innerHTML = '';
        
        this.gameState.players.forEach(player => {
            const playerCard = document.createElement('div');
            playerCard.className = `player-card ${player.alive ? '' : 'dead'}`;
            playerCard.dataset.playerId = player.id;
            
            playerCard.innerHTML = `
                <div class="player-avatar">${player.avatar}</div>
                <div class="player-name">${player.name}</div>
                <div class="player-status">${player.alive ? 'Alive' : 'Dead'}</div>
            `;
            
            // Add selection animation
            playerCard.style.opacity = '0';
            playerCard.style.transform = 'translateY(20px)';
            
            playersGrid.appendChild(playerCard);
            
            // Animate in
            setTimeout(() => {
                playerCard.style.transition = 'all 0.3s ease';
                playerCard.style.opacity = '1';
                playerCard.style.transform = 'translateY(0)';
            }, Math.random() * 500);
        });
    }

    selectPlayer(playerCard) {
        if (playerCard.classList.contains('dead')) return;
        
        const playerId = playerCard.dataset.playerId;
        const player = this.gameState.players.find(p => p.id === playerId);
        
        if (player.id === this.gameState.currentPlayer.id) {
            this.showNotification("You can't target yourself!", 'error');
            return;
        }

        // Clear previous selection
        this.clearPlayerSelection();
        
        // Select new target
        playerCard.classList.add('selected');
        this.gameState.selectedTarget = player;
        
        // Update UI
        this.updateSelectedTarget();
        this.playSoundEffect('action');
    }

    clearPlayerSelection() {
        document.querySelectorAll('.player-card.selected').forEach(card => {
            card.classList.remove('selected');
        });
        this.gameState.selectedTarget = null;
        this.updateSelectedTarget();
    }

    updateSelectedTarget() {
        const selectedTargetEl = document.getElementById('selectedTarget');
        const confirmBtn = document.getElementById('confirmAction');
        
        if (this.gameState.selectedTarget) {
            if (selectedTargetEl) {
                selectedTargetEl.style.display = 'block';
                selectedTargetEl.querySelector('.target-name').textContent = this.gameState.selectedTarget.name;
            }
            if (confirmBtn) {
                confirmBtn.disabled = false;
            }
        } else {
            if (selectedTargetEl) {
                selectedTargetEl.style.display = 'none';
            }
            if (confirmBtn) {
                confirmBtn.disabled = true;
            }
        }
    }

    updatePlayersAlive() {
        const aliveCount = this.gameState.players.filter(p => p.alive).length;
        const totalCount = this.gameState.players.length;
        
        const playersAliveEl = document.querySelector('.players-alive');
        if (playersAliveEl) {
            playersAliveEl.textContent = `👥 ${aliveCount}/${totalCount} alive`;
        }
    }

    // Action Handling
    confirmAction() {
        if (!this.gameState.selectedTarget) return;
        
        const action = this.getCurrentAction();
        const target = this.gameState.selectedTarget;
        
        this.showConfirmDialog(
            `Confirm ${action}`,
            `Are you sure you want to ${action.toLowerCase()} ${target.name}?`,
            () => this.executeAction(action, target)
        );
    }

    getCurrentAction() {
        const phase = this.gameState.phase;
        const role = this.gameState.currentPlayer.role;
        
        if (phase === 'night' && role === 'werewolf') return 'Kill';
        if (phase === 'day' && role === 'seer') return 'Investigate';
        if (phase === 'voting') return 'Vote';
        
        return 'Action';
    }

    executeAction(action, target) {
        this.showLoading(`Executing ${action.toLowerCase()}...`);
        
        // Simulate server communication
        setTimeout(() => {
            this.hideLoading();
            this.showNotification(`${action} confirmed on ${target.name}!`, 'success');
            
            // Clear selection and disable further actions
            this.clearPlayerSelection();
            this.disableActions();
            
            this.playSoundEffect('action');
        }, 1500);
    }

    cancelAction() {
        this.clearPlayerSelection();
        this.showNotification('Action cancelled', 'info');
    }

    disableActions() {
        const confirmBtn = document.getElementById('confirmAction');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.textContent = '✅ Action Completed';
        }
        
        // Disable player selection
        document.querySelectorAll('.player-card').forEach(card => {
            card.style.pointerEvents = 'none';
            card.style.opacity = '0.7';
        });
    }

    // Chat System
    setupChat() {
        this.chatMessages = [
            { id: 1, author: 'System', content: 'Game started! Night phase begins.', time: '23:45', type: 'system', tab: 'all' },
            { id: 2, author: 'AlphaWolf', content: 'Who should we target tonight?', time: '23:46', type: 'werewolf', tab: 'werewolf' },
            { id: 3, author: 'You', content: 'I think Alice looks suspicious...', time: '23:46', type: 'werewolf', tab: 'werewolf' }
        ];
        
        this.renderChatMessages();
    }

    switchChatTab(tab) {
        this.gameState.currentChatTab = tab;
        
        // Update tab appearance
        document.querySelectorAll('.chat-tab').forEach(tabEl => {
            tabEl.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
        
        // Re-render messages for this tab
        this.renderChatMessages();
        
        // Clear notification badge for this tab
        this.clearChatNotification(tab);
    }

    renderChatMessages() {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;
        
        const currentTab = this.gameState.currentChatTab;
        const filteredMessages = this.chatMessages.filter(msg => 
            msg.tab === currentTab || msg.tab === 'all' || currentTab === 'all'
        );
        
        chatMessages.innerHTML = '';
        
        filteredMessages.forEach(message => {
            const messageEl = document.createElement('div');
            messageEl.className = `chat-message ${message.type}`;
            
            messageEl.innerHTML = `
                <div class="message-author">${message.author}</div>
                <div class="message-content">${message.content}</div>
                <div class="message-time">${message.time}</div>
            `;
            
            chatMessages.appendChild(messageEl);
        });
        
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    sendMessage() {
        const chatInput = document.getElementById('chatInput');
        if (!chatInput || !chatInput.value.trim()) return;
        
        const message = {
            id: Date.now(),
            author: 'You',
            content: chatInput.value.trim(),
            time: new Date().toLocaleTimeString().slice(0, 5),
            type: this.gameState.currentChatTab === 'werewolf' ? 'werewolf' : 'normal',
            tab: this.gameState.currentChatTab
        };
        
        this.chatMessages.push(message);
        this.renderChatMessages();
        
        chatInput.value = '';
        this.playSoundEffect('notification');
        
        // Simulate response (in real game, this would come from server)
        if (this.gameState.currentChatTab === 'werewolf') {
            setTimeout(() => {
                this.receiveMessage({
                    id: Date.now(),
                    author: 'AlphaWolf',
                    content: 'Good idea! Let\'s coordinate our votes.',
                    time: new Date().toLocaleTimeString().slice(0, 5),
                    type: 'werewolf',
                    tab: 'werewolf'
                });
            }, 1000 + Math.random() * 2000);
        }
    }

    receiveMessage(message) {
        this.chatMessages.push(message);
        
        if (this.gameState.currentChatTab === message.tab || message.tab === 'all') {
            this.renderChatMessages();
        } else {
            // Show notification badge
            this.showChatNotification(message.tab);
        }
        
        this.playSoundEffect('notification');
    }

    showChatNotification(tab) {
        const badge = document.getElementById('chatBadge');
        if (badge) {
            let count = parseInt(badge.textContent) || 0;
            badge.textContent = count + 1;
            badge.style.display = 'flex';
        }
    }

    clearChatNotification(tab) {
        const badge = document.getElementById('chatBadge');
        if (badge) {
            badge.style.display = 'none';
            badge.textContent = '0';
        }
    }

    // Panel Management
    togglePanel(panelId) {
        const panel = document.getElementById(panelId);
        if (!panel) return;
        
        // Close other panels first
        document.querySelectorAll('.role-sidebar, .game-log-panel, .settings-panel').forEach(p => {
            if (p.id !== panelId) {
                p.classList.remove('open');
            }
        });
        
        panel.classList.toggle('open');
    }

    toggleMobileChat() {
        const chatSection = document.querySelector('.chat-section');
        if (chatSection) {
            chatSection.classList.toggle('mobile-open');
        }
    }

    // Sound Management
    setupSounds() {
        // In a real implementation, you would load actual sound files
        this.sounds = {
            notification: { play: () => console.log('🔊 Notification sound') },
            phaseChange: { play: () => console.log('🔊 Phase change sound') },
            action: { play: () => console.log('🔊 Action sound') }
        };
    }

    playSoundEffect(soundName) {
        if (this.sounds[soundName] && this.getSetting('soundEffects', true)) {
            this.sounds[soundName].play();
        }
    }

    // Settings Management
    getSetting(key, defaultValue = false) {
        const settings = JSON.parse(localStorage.getItem('werewolfSettings') || '{}');
        return settings[key] ?? defaultValue;
    }

    setSetting(key, value) {
        const settings = JSON.parse(localStorage.getItem('werewolfSettings') || '{}');
        settings[key] = value;
        localStorage.setItem('werewolfSettings', JSON.stringify(settings));
    }

    // UI Utilities
    showNotification(message, type = 'info') {
        const container = document.getElementById('notificationContainer');
        if (!container) return;
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="flex items-center gap-sm">
                <span>${this.getNotificationIcon(type)}</span>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="margin-left: auto; background: none; border: none; color: inherit; cursor: pointer;">×</button>
            </div>
        `;
        
        container.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOut 0.3s ease-in forwards';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }

    showLoading(message = 'Loading...') {
        const existing = document.querySelector('.loading-overlay');
        if (existing) existing.remove();
        
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(4px);
        `;
        
        overlay.innerHTML = `
            <div class="glass-card text-center">
                <div class="loading mb-md">
                    <div class="spinner"></div>
                    <span>${message}</span>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
    }

    hideLoading() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) {
            overlay.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => overlay.remove(), 300);
        }
    }

    showConfirmDialog(title, message, onConfirm) {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(4px);
        `;
        
        overlay.innerHTML = `
            <div class="glass-card text-center" style="max-width: 400px;">
                <h3>${title}</h3>
                <p style="margin: var(--space-lg) 0;">${message}</p>
                <div class="flex gap-md justify-center">
                    <button class="btn btn-danger" id="confirmYes">Yes, Confirm</button>
                    <button class="btn btn-ghost" id="confirmNo">Cancel</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        overlay.querySelector('#confirmYes').addEventListener('click', () => {
            overlay.remove();
            onConfirm();
        });
        
        overlay.querySelector('#confirmNo').addEventListener('click', () => {
            overlay.remove();
        });
    }

    confirmLeaveGame() {
        this.showConfirmDialog(
            'Leave Game',
            'Are you sure you want to leave the game? You won\'t be able to rejoin.',
            () => {
                this.showLoading('Leaving game...');
                setTimeout(() => {
                    window.location.href = 'modern-lobby.html';
                }, 1500);
            }
        );
    }
}

// Initialize the game when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.werewolfGame = new WerewolfGame();
});

// Additional CSS for notifications and overlays
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    .notification-container {
        position: fixed;
        top: 100px;
        right: var(--space-lg);
        z-index: 1001;
        display: flex;
        flex-direction: column;
        gap: var(--space-sm);
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    
    @media (max-width: 768px) {
        .notification-container {
            left: var(--space-sm);
            right: var(--space-sm);
            top: 90px;
        }
    }
`;
document.head.appendChild(additionalStyles);