// Modern Werewolf Game - Lobby JavaScript
class WerewolfLobby {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'dark';
        this.currentUser = null;
        this.activeGames = [];
        this.notifications = [];
        
        this.init();
    }

    init() {
        this.setupTheme();
        this.setupEventListeners();
        this.setupAnimations();
        this.loadActiveGames();
        this.startActivityUpdater();
    }

    // Theme Management
    setupTheme() {
        document.documentElement.setAttribute('data-theme', this.theme);
    }

    toggleTheme() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', this.theme);
        localStorage.setItem('theme', this.theme);
        
        // Smooth transition effect
        document.body.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 300);
    }

    // Event Listeners
    setupEventListeners() {
        // Theme toggle
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }

        // Game code input - auto format
        const gameCodeInput = document.getElementById('gameCode');
        if (gameCodeInput) {
            gameCodeInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (e.target.value.length === 6) {
                    this.joinGame(e.target.value);
                }
            });
        }

        // Quick join button
        const quickJoinBtn = document.querySelector('.btn-primary');
        if (quickJoinBtn) {
            quickJoinBtn.addEventListener('click', () => {
                const code = gameCodeInput?.value;
                if (code && code.length === 6) {
                    this.joinGame(code);
                } else {
                    this.showNotification('Please enter a valid 6-character game code', 'error');
                }
            });
        }

        // Create game form
        const createGameForm = document.querySelector('form');
        if (createGameForm) {
            createGameForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.createGame(new FormData(e.target));
            });
        }

        // Join active game buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.game-item .btn-ghost:not([disabled])')) {
                e.preventDefault();
                const gameItem = e.target.closest('.game-item');
                const gameName = gameItem.querySelector('h4').textContent;
                this.joinActiveGame(gameName);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'k':
                        e.preventDefault();
                        gameCodeInput?.focus();
                        break;
                    case 'd':
                        e.preventDefault();
                        this.toggleTheme();
                        break;
                }
            }
        });
    }

    // Animations
    setupAnimations() {
        // Stagger animations for cards
        const cards = document.querySelectorAll('.glass-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 100}ms`;
            card.classList.add('slide-up');
        });

        // Intersection Observer for scroll animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.glass-card').forEach(el => {
            observer.observe(el);
        });
    }

    // Game Management
    async createGame(formData) {
        const gameData = {
            playerName: formData.get('playerName') || 'Anonymous',
            gameMode: formData.get('gameMode') || 'classic',
            maxPlayers: parseInt(formData.get('maxPlayers')) || 10,
            privacy: formData.get('privacy') || 'public'
        };

        this.showLoading('Creating game...');

        try {
            // Simulate API call
            await this.delay(1500);
            
            const gameCode = this.generateGameCode();
            const gameId = Date.now();

            this.showNotification(`Game created! Code: ${gameCode}`, 'success');
            
            // Redirect to waiting room
            setTimeout(() => {
                window.location.href = `waiting-room.html?code=${gameCode}&host=true`;
            }, 2000);

        } catch (error) {
            this.showNotification('Failed to create game. Please try again.', 'error');
        } finally {
            this.hideLoading();
        }
    }

    async joinGame(gameCode) {
        this.showLoading('Joining game...');

        try {
            // Simulate API call to check game exists
            await this.delay(1000);
            
            // Simulate game validation
            if (this.validateGameCode(gameCode)) {
                this.showNotification('Joining game...', 'success');
                setTimeout(() => {
                    window.location.href = `waiting-room.html?code=${gameCode}`;
                }, 1500);
            } else {
                throw new Error('Game not found');
            }

        } catch (error) {
            this.showNotification('Game not found or full. Please check the code.', 'error');
        } finally {
            this.hideLoading();
        }
    }

    async joinActiveGame(gameName) {
        this.showLoading('Joining game...');

        try {
            await this.delay(1000);
            this.showNotification(`Joining "${gameName}"...`, 'success');
            
            setTimeout(() => {
                window.location.href = `waiting-room.html?game=${encodeURIComponent(gameName)}`;
            }, 1500);

        } catch (error) {
            this.showNotification('Failed to join game. It may be full.', 'error');
        } finally {
            this.hideLoading();
        }
    }

    // Active Games Management
    loadActiveGames() {
        // Simulate loading active games
        this.activeGames = [
            {
                name: 'Midnight Hunt',
                host: 'WolfHunter23',
                players: 7,
                maxPlayers: 12,
                status: 'waiting',
                created: Date.now() - 300000 // 5 minutes ago
            },
            {
                name: 'Village Chaos',
                host: 'MysticSeer',
                players: 4,
                maxPlayers: 8,
                status: 'waiting',
                created: Date.now() - 180000 // 3 minutes ago
            },
            {
                name: 'Full Moon Rising',
                host: 'NightOwl99',
                players: 10,
                maxPlayers: 15,
                status: 'playing',
                created: Date.now() - 600000 // 10 minutes ago
            }
        ];

        this.updateActiveGamesDisplay();
    }

    updateActiveGamesDisplay() {
        const gamesList = document.querySelector('.game-list');
        if (!gamesList) return;

        gamesList.innerHTML = '';

        this.activeGames.forEach(game => {
            const gameItem = this.createGameItem(game);
            gamesList.appendChild(gameItem);
        });
    }

    createGameItem(game) {
        const item = document.createElement('div');
        item.className = 'game-item';
        
        const statusClass = game.status === 'waiting' ? 'online' : 'playing';
        const statusText = game.status === 'waiting' ? 'Waiting' : 'In Progress';
        const joinButton = game.status === 'waiting' && game.players < game.maxPlayers 
            ? '<button class="btn btn-ghost">Join</button>'
            : '<button class="btn btn-ghost" disabled>Full</button>';

        item.innerHTML = `
            <div class="flex justify-between items-center">
                <div>
                    <h4>${game.name}</h4>
                    <p class="text-muted">Host: ${game.host} • ${game.players}/${game.maxPlayers} players</p>
                </div>
                <div class="flex gap-sm items-center">
                    <span class="status-badge ${statusClass}">${statusText}</span>
                    ${joinButton}
                </div>
            </div>
        `;

        // Add entrance animation
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 100);

        return item;
    }

    // Activity Updates
    startActivityUpdater() {
        this.updateActivity();
        setInterval(() => this.updateActivity(), 30000); // Update every 30 seconds
    }

    updateActivity() {
        // Simulate new activities
        const activities = [
            { icon: '🎮', text: 'New game "Midnight Hunt" created', time: '2 min ago' },
            { icon: '👥', text: '5 players joined public games', time: '5 min ago' },
            { icon: '🏆', text: 'Game "Village Chaos" completed', time: '8 min ago' },
            { icon: '🌟', text: 'Player "WolfSlayer" earned achievement', time: '12 min ago' },
            { icon: '🔥', text: 'Peak concurrent players: 247', time: '15 min ago' }
        ];

        const activityList = document.querySelector('.activity-list');
        if (!activityList) return;

        activityList.innerHTML = '';
        activities.slice(0, 3).forEach(activity => {
            const item = document.createElement('div');
            item.className = 'activity-item';
            item.innerHTML = `
                <span class="activity-icon">${activity.icon}</span>
                <div>
                    <p>${activity.text}</p>
                    <span class="activity-time">${activity.time}</span>
                </div>
            `;
            activityList.appendChild(item);
        });
    }

    // Utility Functions
    generateGameCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 6; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    validateGameCode(code) {
        // Simulate validation - in real app, this would hit an API
        return code.length === 6 && /^[A-Z0-9]+$/.test(code);
    }

    // Notification System
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="flex items-center gap-sm">
                <span>${this.getNotificationIcon(type)}</span>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="margin-left: auto; background: none; border: none; color: inherit; cursor: pointer;">×</button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
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

    // Loading States
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
            background: rgba(0, 0, 0, 0.5);
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

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Initialize the lobby when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.werewolfLobby = new WerewolfLobby();
});

// Global functions for HTML event handlers
function toggleTheme() {
    window.werewolfLobby?.toggleTheme();
}

// Add slideOut animation
const style = document.createElement('style');
style.textContent = `
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
`;
document.head.appendChild(style);