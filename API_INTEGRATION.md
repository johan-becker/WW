# API Integration - Werwolf Online

## Overview
The Werwolf game system now includes a modern REST API layer that works alongside the existing PHP system without breaking backward compatibility.

## 🌟 New Features Added

### 1. REST API Endpoints (`/api/*`)
- **POST `/api/create-game`** - Create a new game
- **POST `/api/join-game`** - Join an existing game
- **GET `/api/game/{id}`** - Get game state
- **GET `/api/games`** - List available games

### 2. Modern Game Interface (`/game/{id}`)
- Clean, responsive UI for game participation
- Real-time game state updates
- Seamless integration with existing backend
- Fallback to legacy mode

### 3. Smart Routing System
- **`/`** → Routes to modern lobby for new users or existing game for returning players
- **`/lobby`** → Modern lobby interface
- **`/game/{id}`** → Game interface (modern UI)
- **`/Werwolf.php`** → Legacy interface (preserved)
- **`/api/*`** → REST API endpoints

## 🔧 Implementation Details

### API Endpoints

#### Create Game
```javascript
const response = await fetch("/api/create-game", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
        playerName: "Player Name",
        gameMode: "classic",
        maxPlayers: 10
    })
});

const result = await response.json();
// Returns: { success: true, data: { id: 12345, ... } }
```

#### Join Game
```javascript
const response = await fetch("/api/join-game", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
        gameId: 12345,
        playerName: "Player Name"
    })
});

const result = await response.json();
// Returns: { success: true, data: { id: 12345, playerId: 2, ... } }
```

#### Get Game State
```javascript
const response = await fetch("/api/game/12345");
const result = await response.json();
// Returns: { success: true, data: { id: 12345, phase: 0, players: [...], ... } }
```

### File Structure

```
/
├── api.php                 # REST API handler
├── game.php               # Modern game interface
├── index.php              # Smart routing entry point
├── modern-lobby.js        # Updated to use real API
├── .htaccess             # URL rewriting rules
├── Werwolf.php           # Legacy interface (preserved)
└── includes/
    └── security.php      # Security layer with API helpers
```

### Database Integration

The API seamlessly integrates with the existing database structure:
- Uses existing `{gameId}_game` and `{gameId}_spieler` tables
- Maintains compatibility with all existing game logic
- Preserves player cookies for cross-system compatibility
- Utilizes the security improvements (prepared statements, validation)

## 🎮 User Experience Flow

### New User Journey
1. Visit `/` → Redirected to modern lobby
2. Create game → API creates game and database tables
3. Redirected to `/game/{id}` → Modern game interface
4. Other players can join via game ID
5. Game proceeds with real-time updates

### Returning User Journey
1. Visit `/` → Redirected to existing game `/game/{id}`
2. Continue playing from where they left off
3. Can switch to legacy mode with "Legacy-Modus" button

### Legacy User Journey
1. Visit `/?legacy=1` → Classic interface
2. All existing functionality preserved
3. No changes to existing gameplay

## 🔒 Security Features

### API Security
- Input validation on all endpoints
- SQL injection protection via prepared statements
- Error handling without information disclosure
- Rate limiting ready (can be added)
- CORS headers configured

### Game Security
- Game ID validation (10000-99999 range)
- Player name sanitization
- Character and phase validation
- Database transaction safety

## 🌐 Browser Compatibility

### Modern Interface
- Chrome, Firefox, Safari, Edge (latest versions)
- Mobile responsive design
- JavaScript ES6+ features

### Legacy Fallback
- Older browsers automatically use `Werwolf.php`
- Manual fallback via `/?legacy=1`
- No JavaScript required for basic functionality

## 🔄 Integration with Existing System

### Preserved Components
- All existing game logic in `includes/functions.php`
- Database structure unchanged
- Game phases and rules intact
- Character mechanics preserved
- Timer systems maintained

### Enhanced Components
- Secure database access via `DatabaseHelper`
- Modern error handling via `ErrorHandler`
- Input validation via `SecurityHelper`
- REST API layer for modern frontends

## 📊 API Response Format

### Success Response
```json
{
    "success": true,
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "success": false,
    "error": "Error message"
}
```

### Game State Response
```json
{
    "success": true,
    "data": {
        "id": 12345,
        "phase": 0,
        "playerCount": 4,
        "maxPlayers": 50,
        "status": "setup",
        "players": [
            {
                "id": 1,
                "name": "Player 1",
                "alive": true,
                "ready": false,
                "character": "villager"
            }
        ],
        "settings": {
            "werwolfCount": 2,
            "characterReveal": true,
            "mayorHandover": true
        }
    }
}
```

## 🚀 Testing

### API Testing
```bash
# Create a game
curl -X POST http://localhost/api/create-game \
  -H "Content-Type: application/json" \
  -d '{"playerName":"Test Player","gameMode":"classic"}'

# Join a game
curl -X POST http://localhost/api/join-game \
  -H "Content-Type: application/json" \
  -d '{"gameId":12345,"playerName":"Player 2"}'

# Get game state
curl http://localhost/api/game/12345
```

### Frontend Testing
1. Visit `/lobby` to test modern interface
2. Create a game and verify redirect to `/game/{id}`
3. Open second browser/tab to join the same game
4. Test legacy mode toggle

## 🔮 Future Enhancements

### Planned Features
- WebSocket support for real-time updates
- Player authentication system
- Game spectator mode
- Advanced game statistics
- Tournament mode
- Mobile app API endpoints

### Performance Optimizations
- Redis caching for game states
- Database connection pooling
- API response caching
- CDN integration for static assets

---

**Note**: This integration preserves full backward compatibility while adding modern features. Existing users can continue using the legacy interface without any changes, while new users get a modern experience.

**Last Updated**: 2025-07-25
**Version**: 1.0.0
**Compatibility**: PHP 7.4+, MySQL 5.7+