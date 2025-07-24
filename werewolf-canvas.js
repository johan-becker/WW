// Werewolf Canvas Graphics Generator
// Creates beautiful werewolf-themed images using HTML5 Canvas

class WerewolfCanvas {
    constructor() {
        this.canvas = document.createElement('canvas');
        this.ctx = this.canvas.getContext('2d');
    }

    // Create werewolf head icon
    createWerewolfIcon(size = 64) {
        this.canvas.width = size;
        this.canvas.height = size;
        const ctx = this.ctx;
        const center = size / 2;

        // Clear canvas
        ctx.clearRect(0, 0, size, size);

        // Create gradient background
        const bgGradient = ctx.createRadialGradient(center, center, 0, center, center, size/2);
        bgGradient.addColorStop(0, 'rgba(26, 26, 46, 0.8)');
        bgGradient.addColorStop(1, 'rgba(0, 0, 0, 0.9)');
        
        ctx.beginPath();
        ctx.arc(center, center, size/2 - 2, 0, Math.PI * 2);
        ctx.fillStyle = bgGradient;
        ctx.fill();

        // Wolf head outline
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Wolf ears
        this.drawEar(ctx, center - size/4, center - size/3, size/6);
        this.drawEar(ctx, center + size/4, center - size/3, size/6);

        // Wolf snout
        this.drawSnout(ctx, center, center + size/6, size/4);

        // Wolf eyes
        this.drawEye(ctx, center - size/6, center - size/8, size/12);
        this.drawEye(ctx, center + size/6, center - size/8, size/12);

        // Wolf nose
        this.drawNose(ctx, center, center + size/12, size/20);

        return this.canvas.toDataURL();
    }

    // Create moon icon
    createMoonIcon(size = 64) {
        this.canvas.width = size;
        this.canvas.height = size;
        const ctx = this.ctx;
        const center = size / 2;

        ctx.clearRect(0, 0, size, size);

        // Moon gradient
        const moonGradient = ctx.createRadialGradient(center - size/8, center - size/8, 0, center, center, size/2);
        moonGradient.addColorStop(0, '#ffd700');
        moonGradient.addColorStop(0.7, '#d4af37');
        moonGradient.addColorStop(1, '#b8860b');

        // Draw moon circle
        ctx.beginPath();
        ctx.arc(center, center, size/2 - 4, 0, Math.PI * 2);
        ctx.fillStyle = moonGradient;
        ctx.fill();

        // Moon glow
        ctx.shadowColor = '#ffd700';
        ctx.shadowBlur = 10;
        ctx.stroke();

        // Moon craters
        this.drawCrater(ctx, center - size/6, center - size/8, size/16);
        this.drawCrater(ctx, center + size/8, center + size/6, size/20);
        this.drawCrater(ctx, center - size/12, center + size/4, size/24);

        return this.canvas.toDataURL();
    }

    // Helper methods for drawing wolf features
    drawEar(ctx, x, y, size) {
        ctx.save();
        ctx.fillStyle = '#4a4a4a';
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 1;

        ctx.beginPath();
        ctx.ellipse(x, y, size/2, size, Math.PI/6, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();

        // Inner ear
        ctx.fillStyle = '#2a2a2a';
        ctx.beginPath();
        ctx.ellipse(x, y, size/3, size/1.5, Math.PI/6, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
    }

    drawSnout(ctx, x, y, size) {
        ctx.save();
        ctx.fillStyle = '#5a5a5a';
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 1;

        ctx.beginPath();
        ctx.ellipse(x, y, size, size/2, 0, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();

        ctx.restore();
    }

    drawEye(ctx, x, y, size) {
        ctx.save();
        
        // Eye background
        ctx.fillStyle = '#8b0000';
        ctx.beginPath();
        ctx.ellipse(x, y, size, size/1.2, 0, 0, Math.PI * 2);
        ctx.fill();

        // Eye pupil
        ctx.fillStyle = '#ff0000';
        ctx.beginPath();
        ctx.ellipse(x, y, size/2, size/2, 0, 0, Math.PI * 2);
        ctx.fill();

        // Eye highlight
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.ellipse(x - size/4, y - size/4, size/4, size/4, 0, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
    }

    drawNose(ctx, x, y, size) {
        ctx.save();
        ctx.fillStyle = '#222';
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 1;

        ctx.beginPath();
        ctx.ellipse(x, y, size, size/1.5, 0, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();

        ctx.restore();
    }

    drawCrater(ctx, x, y, size) {
        ctx.save();
        ctx.fillStyle = 'rgba(0, 0, 0, 0.3)';
        ctx.beginPath();
        ctx.arc(x, y, size, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    // Character Portrait System
    createCharacterPortrait(character, size = 80) {
        this.canvas.width = size;
        this.canvas.height = size;
        const ctx = this.ctx;
        const center = size / 2;

        ctx.clearRect(0, 0, size, size);

        switch(character.toLowerCase()) {
            case 'werewolf':
            case 'werwolf':
                return this.drawWerewolfCharacter(ctx, center, size);
            case 'villager':
            case 'bürger':
                return this.drawVillagerCharacter(ctx, center, size);
            case 'witch':
            case 'hexe':
                return this.drawWitchCharacter(ctx, center, size);
            case 'hunter':
            case 'jäger':
                return this.drawHunterCharacter(ctx, center, size);
            case 'seer':
            case 'seher':
                return this.drawSeerCharacter(ctx, center, size);
            case 'mayor':
            case 'bürgermeister':
                return this.drawMayorCharacter(ctx, center, size);
            case 'bodyguard':
            case 'beschützer':
                return this.drawBodyguardCharacter(ctx, center, size);
            case 'cupid':
            case 'amor':
                return this.drawCupidCharacter(ctx, center, size);
            default:
                return this.drawVillagerCharacter(ctx, center, size);
        }
    }

    drawWerewolfCharacter(ctx, center, size) {
        // Dark background with red glow
        const bgGradient = ctx.createRadialGradient(center, center, 0, center, center, size/2);
        bgGradient.addColorStop(0, 'rgba(139, 0, 0, 0.8)');
        bgGradient.addColorStop(1, 'rgba(0, 0, 0, 0.9)');
        
        ctx.beginPath();
        ctx.arc(center, center, size/2 - 2, 0, Math.PI * 2);
        ctx.fillStyle = bgGradient;
        ctx.fill();
        ctx.strokeStyle = '#8b0000';
        ctx.lineWidth = 3;
        ctx.stroke();

        // Werewolf features
        this.drawWerewolfFace(ctx, center, center, size/2.5);
        return this.canvas.toDataURL();
    }

    drawVillagerCharacter(ctx, center, size) {
        // Warm background
        const bgGradient = ctx.createRadialGradient(center, center, 0, center, center, size/2);
        bgGradient.addColorStop(0, 'rgba(139, 101, 8, 0.6)');
        bgGradient.addColorStop(1, 'rgba(44, 24, 16, 0.8)');
        
        ctx.beginPath();
        ctx.arc(center, center, size/2 - 2, 0, Math.PI * 2);
        ctx.fillStyle = bgGradient;
        ctx.fill();
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Human face
        this.drawHumanFace(ctx, center, center, size/3);
        
        // Simple clothing
        ctx.fillStyle = '#8b4513';
        ctx.fillRect(center - size/6, center + size/4, size/3, size/6);
        
        return this.canvas.toDataURL();
    }

    drawWitchCharacter(ctx, center, size) {
        // Purple mystical background
        const bgGradient = ctx.createRadialGradient(center, center, 0, center, center, size/2);
        bgGradient.addColorStop(0, 'rgba(75, 0, 130, 0.7)');
        bgGradient.addColorStop(1, 'rgba(25, 0, 51, 0.9)');
        
        ctx.beginPath();
        ctx.arc(center, center, size/2 - 2, 0, Math.PI * 2);
        ctx.fillStyle = bgGradient;
        ctx.fill();
        ctx.strokeStyle = '#9370db';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Witch hat
        this.drawWitchHat(ctx, center, center - size/4, size/4);
        
        // Face
        this.drawHumanFace(ctx, center, center, size/4);
        
        // Mystical aura
        this.drawMysticalAura(ctx, center, center, size/2);
        
        return this.canvas.toDataURL();
    }

    drawHunterCharacter(ctx, center, size) {
        // Forest green background
        const bgGradient = ctx.createRadialGradient(center, center, 0, center, center, size/2);
        bgGradient.addColorStop(0, 'rgba(34, 139, 34, 0.6)');
        bgGradient.addColorStop(1, 'rgba(0, 50, 0, 0.8)');
        
        ctx.beginPath();
        ctx.arc(center, center, size/2 - 2, 0, Math.PI * 2);
        ctx.fillStyle = bgGradient;
        ctx.fill();
        ctx.strokeStyle = '#228b22';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Hunter's crossbow
        this.drawCrossbow(ctx, center + size/4, center - size/6, size/6);
        
        // Face with determined expression
        this.drawHumanFace(ctx, center, center, size/4);
        
        // Hunter's cloak
        ctx.fillStyle = '#556b2f';
        ctx.fillRect(center - size/5, center + size/6, size/2.5, size/4);
        
        return this.canvas.toDataURL();
    }

    drawSeerCharacter(ctx, center, size) {
        // Mystical blue background
        const bgGradient = ctx.createRadialGradient(center, center, 0, center, center, size/2);
        bgGradient.addColorStop(0, 'rgba(70, 130, 180, 0.6)');
        bgGradient.addColorStop(1, 'rgba(25, 25, 112, 0.8)');
        
        ctx.beginPath();
        ctx.arc(center, center, size/2 - 2, 0, Math.PI * 2);
        ctx.fillStyle = bgGradient;
        ctx.fill();
        ctx.strokeStyle = '#4682b4';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Crystal ball
        this.drawCrystalBall(ctx, center, center + size/4, size/8);
        
        // Wise face
        this.drawHumanFace(ctx, center, center - size/8, size/4);
        
        // Magical sparkles
        this.drawSparkles(ctx, center, center, size/2);
        
        return this.canvas.toDataURL();
    }

    drawMayorCharacter(ctx, center, size) {
        // Royal gold background
        const bgGradient = ctx.createRadialGradient(center, center, 0, center, center, size/2);
        bgGradient.addColorStop(0, 'rgba(255, 215, 0, 0.7)');
        bgGradient.addColorStop(1, 'rgba(184, 134, 11, 0.8)');
        
        ctx.beginPath();
        ctx.arc(center, center, size/2 - 2, 0, Math.PI * 2);
        ctx.fillStyle = bgGradient;
        ctx.fill();
        ctx.strokeStyle = '#ffd700';
        ctx.lineWidth = 3;
        ctx.stroke();

        // Crown
        this.drawCrown(ctx, center, center - size/3, size/5);
        
        // Noble face
        this.drawHumanFace(ctx, center, center, size/4);
        
        // Formal attire
        ctx.fillStyle = '#800080';
        ctx.fillRect(center - size/4, center + size/6, size/2, size/4);
        
        return this.canvas.toDataURL();
    }

    drawBodyguardCharacter(ctx, center, size) {
        // Steel blue background
        const bgGradient = ctx.createRadialGradient(center, center, 0, center, center, size/2);
        bgGradient.addColorStop(0, 'rgba(70, 130, 180, 0.6)');
        bgGradient.addColorStop(1, 'rgba(47, 79, 79, 0.8)');
        
        ctx.beginPath();
        ctx.arc(center, center, size/2 - 2, 0, Math.PI * 2);
        ctx.fillStyle = bgGradient;
        ctx.fill();
        ctx.strokeStyle = '#4682b4';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Shield
        this.drawShield(ctx, center - size/4, center, size/4);
        
        // Strong face
        this.drawHumanFace(ctx, center, center, size/4);
        
        // Armor
        ctx.fillStyle = '#708090';
        ctx.fillRect(center - size/6, center + size/6, size/3, size/4);
        
        return this.canvas.toDataURL();
    }

    drawCupidCharacter(ctx, center, size) {
        // Pink romantic background
        const bgGradient = ctx.createRadialGradient(center, center, 0, center, center, size/2);
        bgGradient.addColorStop(0, 'rgba(255, 192, 203, 0.7)');
        bgGradient.addColorStop(1, 'rgba(219, 112, 147, 0.8)');
        
        ctx.beginPath();
        ctx.arc(center, center, size/2 - 2, 0, Math.PI * 2);
        ctx.fillStyle = bgGradient;
        ctx.fill();
        ctx.strokeStyle = '#ff69b4';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Angel wings
        this.drawWings(ctx, center, center, size/3);
        
        // Cherub face
        this.drawHumanFace(ctx, center, center + size/8, size/5);
        
        // Bow and arrow
        this.drawBowAndArrow(ctx, center + size/4, center - size/6, size/6);
        
        return this.canvas.toDataURL();
    }

    // Helper methods for character features
    drawWerewolfFace(ctx, x, y, size) {
        ctx.save();
        
        // Wolf snout
        ctx.fillStyle = '#333';
        ctx.beginPath();
        ctx.ellipse(x, y + size/3, size/2, size/3, 0, 0, Math.PI * 2);
        ctx.fill();
        
        // Fangs
        ctx.fillStyle = '#fff';
        ctx.beginPath();
        ctx.moveTo(x - size/6, y + size/4);
        ctx.lineTo(x - size/8, y + size/2);
        ctx.lineTo(x - size/12, y + size/4);
        ctx.fill();
        
        ctx.beginPath();
        ctx.moveTo(x + size/6, y + size/4);
        ctx.lineTo(x + size/8, y + size/2);
        ctx.lineTo(x + size/12, y + size/4);
        ctx.fill();
        
        // Glowing red eyes
        ctx.fillStyle = '#ff0000';
        ctx.beginPath();
        ctx.ellipse(x - size/4, y - size/6, size/8, size/6, 0, 0, Math.PI * 2);
        ctx.fill();
        ctx.beginPath();
        ctx.ellipse(x + size/4, y - size/6, size/8, size/6, 0, 0, Math.PI * 2);
        ctx.fill();
        
        ctx.restore();
    }

    drawHumanFace(ctx, x, y, size) {
        ctx.save();
        
        // Face
        ctx.fillStyle = '#deb887';
        ctx.beginPath();
        ctx.arc(x, y, size, 0, Math.PI * 2);
        ctx.fill();
        
        // Eyes
        ctx.fillStyle = '#000';
        ctx.beginPath();
        ctx.arc(x - size/3, y - size/6, size/8, 0, Math.PI * 2);
        ctx.fill();
        ctx.beginPath();
        ctx.arc(x + size/3, y - size/6, size/8, 0, Math.PI * 2);
        ctx.fill();
        
        // Mouth
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(x, y + size/4, size/4, 0, Math.PI);
        ctx.stroke();
        
        ctx.restore();
    }

    drawWitchHat(ctx, x, y, size) {
        ctx.save();
        ctx.fillStyle = '#2f0f2f';
        ctx.beginPath();
        ctx.moveTo(x, y - size);
        ctx.lineTo(x - size/2, y);
        ctx.lineTo(x + size/2, y);
        ctx.closePath();
        ctx.fill();
        
        // Hat brim
        ctx.beginPath();
        ctx.ellipse(x, y, size, size/4, 0, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    drawMysticalAura(ctx, x, y, size) {
        ctx.save();
        ctx.strokeStyle = '#9370db';
        ctx.lineWidth = 2;
        for (let i = 0; i < 6; i++) {
            const angle = (i * Math.PI * 2) / 6;
            const startX = x + Math.cos(angle) * size/2;
            const startY = y + Math.sin(angle) * size/2;
            const endX = x + Math.cos(angle) * size/1.5;
            const endY = y + Math.sin(angle) * size/1.5;
            
            ctx.beginPath();
            ctx.moveTo(startX, startY);
            ctx.lineTo(endX, endY);
            ctx.stroke();
        }
        ctx.restore();
    }

    drawCrossbow(ctx, x, y, size) {
        ctx.save();
        ctx.strokeStyle = '#8b4513';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(x - size, y);
        ctx.lineTo(x + size, y);
        ctx.moveTo(x, y - size/2);
        ctx.lineTo(x, y + size/2);
        ctx.stroke();
        ctx.restore();
    }

    drawCrystalBall(ctx, x, y, size) {
        ctx.save();
        const gradient = ctx.createRadialGradient(x, y, 0, x, y, size);
        gradient.addColorStop(0, 'rgba(255, 255, 255, 0.8)');
        gradient.addColorStop(1, 'rgba(173, 216, 230, 0.6)');
        
        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.arc(x, y, size, 0, Math.PI * 2);
        ctx.fill();
        
        ctx.strokeStyle = '#4682b4';
        ctx.lineWidth = 2;
        ctx.stroke();
        ctx.restore();
    }

    drawSparkles(ctx, x, y, size) {
        ctx.save();
        ctx.fillStyle = '#fff';
        for (let i = 0; i < 8; i++) {
            const angle = (i * Math.PI * 2) / 8;
            const sparkleX = x + Math.cos(angle) * size/3;
            const sparkleY = y + Math.sin(angle) * size/3;
            
            ctx.beginPath();
            ctx.arc(sparkleX, sparkleY, 2, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.restore();
    }

    drawCrown(ctx, x, y, size) {
        ctx.save();
        ctx.fillStyle = '#ffd700';
        ctx.beginPath();
        ctx.moveTo(x - size, y);
        ctx.lineTo(x - size/2, y - size/2);
        ctx.lineTo(x, y - size);
        ctx.lineTo(x + size/2, y - size/2);
        ctx.lineTo(x + size, y);
        ctx.lineTo(x - size, y);
        ctx.fill();
        
        // Gems
        ctx.fillStyle = '#ff0000';
        ctx.beginPath();
        ctx.arc(x, y - size/2, size/8, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    }

    drawShield(ctx, x, y, size) {
        ctx.save();
        ctx.fillStyle = '#c0c0c0';
        ctx.beginPath();
        ctx.moveTo(x, y - size);
        ctx.lineTo(x - size/2, y - size/2);
        ctx.lineTo(x - size/2, y + size/2);
        ctx.lineTo(x, y + size);
        ctx.lineTo(x + size/2, y + size/2);
        ctx.lineTo(x + size/2, y - size/2);
        ctx.closePath();
        ctx.fill();
        
        ctx.strokeStyle = '#808080';
        ctx.lineWidth = 2;
        ctx.stroke();
        ctx.restore();
    }

    drawWings(ctx, x, y, size) {
        ctx.save();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
        
        // Left wing
        ctx.beginPath();
        ctx.ellipse(x - size/2, y, size/2, size/4, -Math.PI/6, 0, Math.PI * 2);
        ctx.fill();
        
        // Right wing
        ctx.beginPath();
        ctx.ellipse(x + size/2, y, size/2, size/4, Math.PI/6, 0, Math.PI * 2);
        ctx.fill();
        
        ctx.restore();
    }

    drawBowAndArrow(ctx, x, y, size) {
        ctx.save();
        ctx.strokeStyle = '#8b4513';
        ctx.lineWidth = 2;
        
        // Bow
        ctx.beginPath();
        ctx.arc(x, y, size, Math.PI/4, 3*Math.PI/4, false);
        ctx.stroke();
        
        // Arrow
        ctx.beginPath();
        ctx.moveTo(x - size/2, y);
        ctx.lineTo(x + size/2, y);
        ctx.stroke();
        
        ctx.restore();
    }

    // Generate and set favicon
    generateFavicon() {
        const iconData = this.createWerewolfIcon(32);
        
        // Remove existing favicon
        const existingFavicon = document.querySelector("link[rel*='icon']");
        if (existingFavicon) {
            existingFavicon.parentNode.removeChild(existingFavicon);
        }

        // Add new canvas-generated favicon
        const favicon = document.createElement('link');
        favicon.rel = 'icon';
        favicon.type = 'image/png';
        favicon.href = iconData;
        document.head.appendChild(favicon);
    }

    // Generate werewolf logo for header
    generateHeaderLogo(targetId, size = 48) {
        const logoData = this.createWerewolfIcon(size);
        const target = document.getElementById(targetId);
        
        if (target) {
            const img = document.createElement('img');
            img.src = logoData;
            img.style.cssText = `
                width: ${size}px;
                height: ${size}px;
                display: inline-block;
                vertical-align: middle;
                margin: 0 10px;
                filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.5));
                animation: logoSpin 20s linear infinite;
            `;
            target.appendChild(img);
        }
    }

    // Generate moon decoration
    generateMoonDecoration(targetId, size = 40) {
        const moonData = this.createMoonIcon(size);
        const target = document.getElementById(targetId);
        
        if (target) {
            const img = document.createElement('img');
            img.src = moonData;
            img.style.cssText = `
                width: ${size}px;
                height: ${size}px;
                display: inline-block;
                vertical-align: middle;
                margin: 0 10px;
                filter: drop-shadow(0 0 15px rgba(255, 215, 0, 0.6));
                animation: moonGlow 4s ease-in-out infinite alternate;
            `;
            target.appendChild(img);
        }
    }
}

// Initialize and create werewolf graphics when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const werewolfCanvas = new WerewolfCanvas();
    
    // Generate favicon
    werewolfCanvas.generateFavicon();
    
    // Add canvas-generated werewolf icons to header
    setTimeout(() => {
        const title = document.getElementById('main-title');
        if (title) {
            // Replace emoji werewolf with canvas werewolf
            const werewolfIcon = document.createElement('canvas');
            werewolfIcon.width = 48;
            werewolfIcon.height = 48;
            werewolfIcon.style.cssText = `
                display: inline-block;
                vertical-align: middle;
                margin: 0 8px;
                filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.5));
                animation: logoSpin 20s linear infinite;
            `;
            
            const ctx = werewolfIcon.getContext('2d');
            werewolfCanvas.canvas = werewolfIcon;
            werewolfCanvas.ctx = ctx;
            werewolfCanvas.createWerewolfIcon(48);
            
            // Replace the emoji with canvas icon
            title.innerHTML = title.innerHTML.replace('🐺', '');
            title.appendChild(werewolfIcon);
            
            // Add a moon canvas icon too
            const moonIcon = document.createElement('canvas');
            moonIcon.width = 40;
            moonIcon.height = 40;
            moonIcon.style.cssText = `
                display: inline-block;
                vertical-align: middle;
                margin: 0 8px;
                filter: drop-shadow(0 0 15px rgba(255, 215, 0, 0.6));
                animation: moonGlow 4s ease-in-out infinite alternate;
            `;
            
            const moonCtx = moonIcon.getContext('2d');
            werewolfCanvas.canvas = moonIcon;
            werewolfCanvas.ctx = moonCtx;
            werewolfCanvas.createMoonIcon(40);
            
            // Replace moon emoji with canvas icon
            title.innerHTML = title.innerHTML.replace('🌙', '');
            title.insertBefore(moonIcon, title.firstChild);
        }
    }, 500);
    
    // Add logo animations to CSS if not already present
    if (!document.querySelector('#werewolf-animations')) {
        const style = document.createElement('style');
        style.id = 'werewolf-animations';
        style.textContent = `
            @keyframes logoSpin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            
            @keyframes moonGlow {
                0% { filter: drop-shadow(0 0 15px rgba(255, 215, 0, 0.6)); }
                100% { filter: drop-shadow(0 0 25px rgba(255, 215, 0, 1)); }
            }
            
            .player-portrait {
                width: 32px;
                height: 32px;
                display: inline-block;
                vertical-align: middle;
                margin-right: 8px;
                border-radius: 50%;
                transition: transform 0.3s ease, filter 0.3s ease;
            }
            
            .player-portrait:hover {
                transform: scale(1.1) rotate(5deg);
                filter: brightness(1.2) drop-shadow(0 0 10px rgba(212, 175, 55, 0.8));
                animation: portraitGlow 1s ease-in-out infinite alternate;
            }
            
            .player-list-item {
                display: flex;
                align-items: center;
                padding: 8px;
                margin: 4px 0;
                background: rgba(26, 26, 46, 0.3);
                border-radius: 8px;
                transition: background 0.3s ease;
            }
            
            .player-list-item:hover {
                background: rgba(26, 26, 46, 0.5);
            }
            
            @keyframes portraitGlow {
                0% { filter: brightness(1.2) drop-shadow(0 0 10px rgba(212, 175, 55, 0.8)); }
                100% { filter: brightness(1.4) drop-shadow(0 0 20px rgba(212, 175, 55, 1)); }
            }
            
            .werewolf-portrait {
                animation: werewolfPulse 3s ease-in-out infinite;
            }
            
            @keyframes werewolfPulse {
                0%, 100% { filter: drop-shadow(0 0 5px rgba(139, 0, 0, 0.6)); }
                50% { filter: drop-shadow(0 0 15px rgba(255, 0, 0, 0.8)); }
            }
            
            .seer-portrait {
                animation: seerSparkle 4s ease-in-out infinite;
            }
            
            @keyframes seerSparkle {
                0%, 100% { filter: drop-shadow(0 0 5px rgba(70, 130, 180, 0.6)); }
                50% { filter: drop-shadow(0 0 15px rgba(173, 216, 230, 1)); }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Make werewolfCanvas globally available for character portraits
    window.werewolfCanvas = werewolfCanvas;
    
    // Initialize modern features
    initializeModernFeatures();
});

// Modern Features Integration
function initializeModernFeatures() {
    setupThemeToggle();
    setupModernNotifications();
    setupKeyboardShortcuts();
    setupSmoothAnimations();
    setupRoleEnhancements();
}

// Theme Toggle Functionality
function setupThemeToggle() {
    // Create theme toggle button if it doesn't exist
    if (!document.querySelector('.theme-toggle')) {
        const themeToggle = document.createElement('button');
        themeToggle.className = 'theme-toggle';
        themeToggle.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>
        `;
        themeToggle.onclick = toggleTheme;
        document.body.appendChild(themeToggle);
    }
    
    // Apply saved theme
    const savedTheme = localStorage.getItem('werewolf-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('werewolf-theme', newTheme);
    
    // Update theme toggle icon
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        const icon = newTheme === 'dark' ? 
            `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>` :
            `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>`;
        themeToggle.innerHTML = icon;
    }
    
    showNotification(`Switched to ${newTheme} theme`, 'success');
}

// Modern Notification System
function setupModernNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('notificationContainer')) {
        const container = document.createElement('div');
        container.id = 'notificationContainer';
        container.style.cssText = `
            position: fixed;
            top: var(--space-lg, 1.5rem);
            right: var(--space-lg, 1.5rem);
            z-index: 1001;
            display: flex;
            flex-direction: column;
            gap: var(--space-sm, 0.5rem);
        `;
        document.body.appendChild(container);
    }
}

function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    
    notification.innerHTML = `
        <div class="flex items-center gap-sm">
            <span>${icons[type] || icons.info}</span>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" 
                    style="margin-left: auto; background: none; border: none; color: inherit; cursor: pointer; font-size: 1.2rem;">×</button>
        </div>
    `;
    
    container.appendChild(notification);
    
    // Auto remove
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOut 0.3s ease-in forwards';
            setTimeout(() => notification.remove(), 300);
        }
    }, duration);
}

// Keyboard Shortcuts
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey || e.metaKey) {
            switch (e.key) {
                case 'd':
                    e.preventDefault();
                    toggleTheme();
                    break;
                case 'h':
                    e.preventDefault();
                    showHelpModal();
                    break;
                case 'Enter':
                    if (e.target.matches('input[type="text"]')) {
                        e.preventDefault();
                        const form = e.target.closest('form');
                        if (form) {
                            const submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
                            if (submitBtn) submitBtn.click();
                        }
                    }
                    break;
            }
        }
        
        // Escape key to close modals/panels
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
}

function showHelpModal() {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
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
    
    modal.innerHTML = `
        <div class="glass-card" style="max-width: 500px; max-height: 80vh; overflow-y: auto;">
            <div class="flex justify-between items-center mb-lg">
                <h2>🎮 Keyboard Shortcuts</h2>
                <button onclick="this.closest('.modal-overlay').remove()" class="btn btn-ghost">×</button>
            </div>
            <div class="help-content">
                <div class="shortcut-item">
                    <kbd>Ctrl + D</kbd>
                    <span>Toggle dark/light theme</span>
                </div>
                <div class="shortcut-item">
                    <kbd>Ctrl + H</kbd>
                    <span>Show this help</span>
                </div>
                <div class="shortcut-item">
                    <kbd>Ctrl + Enter</kbd>
                    <span>Submit form</span>
                </div>
                <div class="shortcut-item">
                    <kbd>Escape</kbd>
                    <span>Close modals</span>
                </div>
            </div>
        </div>
    `;
    
    // Add modal styles
    const style = document.createElement('style');
    style.textContent = `
        .shortcut-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-sm, 0.5rem) 0;
            border-bottom: 1px solid var(--border-secondary, rgba(255,255,255,0.05));
        }
        .shortcut-item:last-child {
            border-bottom: none;
        }
        kbd {
            background: var(--bg-tertiary, #2d2d2d);
            border: 1px solid var(--border-primary, rgba(255,255,255,0.1));
            border-radius: var(--radius-sm, 0.375rem);
            padding: var(--space-xs, 0.25rem) var(--space-sm, 0.5rem);
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(modal);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

function closeAllModals() {
    document.querySelectorAll('.modal-overlay').forEach(modal => modal.remove());
}

// Smooth Animations
function setupSmoothAnimations() {
    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
            }
        });
    }, { threshold: 0.1 });
    
    // Observe elements for animation
    document.querySelectorAll('#gameselect, #PlayerLog, #gamelogdiv').forEach(el => {
        if (el) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            observer.observe(el);
        }
    });
    
    // Add fadeInUp animation
    if (!document.querySelector('#modern-animations')) {
        const style = document.createElement('style');
        style.id = 'modern-animations';
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
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
            
            /* Enhanced button animations */
            button, input[type=submit] {
                position: relative;
                overflow: hidden;
            }
            
            button::before, input[type=submit]::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                transition: left 0.6s ease;
            }
            
            button:hover::before, input[type=submit]:hover::before {
                left: 100%;
            }
            
            /* Loading state */
            .loading {
                display: inline-flex;
                align-items: center;
                gap: var(--space-sm, 0.5rem);
            }
            
            .loading::after {
                content: '';
                width: 16px;
                height: 16px;
                border: 2px solid var(--border-primary, rgba(255,255,255,0.1));
                border-top: 2px solid var(--accent-primary, #6366f1);
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    }
}

// Role-based Enhancements
function setupRoleEnhancements() {
    // Add modern styling to existing game elements
    enhanceGameElements();
    
    // Setup real-time updates
    startRealtimeUpdates();
}

function enhanceGameElements() {
    // Enhance player log messages
    const playerLog = document.getElementById('PlayerLog');
    if (playerLog) {
        playerLog.classList.add('modern-chat-container');
        
        // Add role-based styling to messages
        const messages = playerLog.querySelectorAll('p');
        messages.forEach(message => {
            message.classList.add('modern-chat-message');
            
            // Detect message types and add appropriate styling
            const text = message.textContent.toLowerCase();
            if (text.includes('werwolf') || text.includes('werewolf')) {
                message.classList.add('werewolf');
            } else if (text.includes('system') || text.includes('spiel')) {
                message.classList.add('system');
            }
        });
    }
    
    // Enhance game log
    const gameLog = document.getElementById('gamelogdiv');
    if (gameLog) {
        const messages = gameLog.querySelectorAll('p');
        messages.forEach(message => {
            message.classList.add('modern-chat-message');
        });
    }
    
    // Add glass card styling to main sections
    const mainSections = document.querySelectorAll('#gameselect, #PlayerLog, #gamelogdiv, #listdiv');
    mainSections.forEach(section => {
        if (section && !section.classList.contains('glass-card')) {
            section.classList.add('glass-card');
        }
    });
}

function startRealtimeUpdates() {
    // Simulate real-time game updates
    let updateInterval;
    
    function checkForUpdates() {
        // This would normally connect to your PHP backend
        // For now, we'll just enhance any new elements that appear
        enhanceNewElements();
    }
    
    function enhanceNewElements() {
        // Look for new messages or elements that need modern styling
        const newMessages = document.querySelectorAll('p:not(.modern-chat-message)');
        newMessages.forEach(message => {
            if (message.closest('#PlayerLog, #gamelogdiv')) {
                message.classList.add('modern-chat-message');
                message.style.animation = 'messageSlide 0.3s ease-out';
            }
        });
        
        // Add portraits to new players
        addCharacterPortraits();
    }
    
    // Start checking for updates every 2 seconds
    updateInterval = setInterval(checkForUpdates, 2000);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (updateInterval) clearInterval(updateInterval);
    });
}

// Enhanced Player Management
function enhancePlayerInteractions() {
    // Add click handlers for modern player cards
    document.addEventListener('click', (e) => {
        const playerCard = e.target.closest('.modern-player-card');
        if (playerCard) {
            handlePlayerCardClick(playerCard);
        }
    });
}

function handlePlayerCardClick(playerCard) {
    const playerId = playerCard.dataset.playerId;
    const playerName = playerCard.querySelector('.player-name')?.textContent;
    
    // Remove previous selections
    document.querySelectorAll('.modern-player-card.selected').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selection to clicked card
    playerCard.classList.add('selected');
    
    // Show selection feedback
    showNotification(`Selected ${playerName}`, 'info', 2000);
    
    // Enable confirm button if present
    const confirmBtn = document.querySelector('#confirmAction, .confirm-action');
    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.classList.add('btn-danger');
    }
}

// Form Enhancement
function enhanceFormSubmissions() {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        const submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
        
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            // Re-enable after a delay (in case form doesn't redirect)
            setTimeout(() => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }, 5000);
        }
    });
}

// Global functions for PHP integration
window.modernWerewolf = {
    showNotification,
    toggleTheme,
    enhancePlayerList,
    addCharacterPortraits,
    mapCharacterName
};

// Function to add character portraits to player lists
function addCharacterPortraits() {
    if (!window.werewolfCanvas) return;
    
    // Find all player name elements and add portraits
    const playerElements = document.querySelectorAll('[data-player-character]');
    
    playerElements.forEach(element => {
        const character = element.getAttribute('data-player-character');
        const playerName = element.textContent;
        
        // Create portrait canvas
        const portrait = document.createElement('canvas');
        portrait.width = 32;
        portrait.height = 32;
        portrait.className = 'player-portrait';
        
        const ctx = portrait.getContext('2d');
        window.werewolfCanvas.canvas = portrait;
        window.werewolfCanvas.ctx = ctx;
        
        // Generate character portrait
        window.werewolfCanvas.createCharacterPortrait(character, 32);
        
        // Add character-specific animation class
        if (character.toLowerCase().includes('werewolf') || character.toLowerCase().includes('werwolf')) {
            portrait.classList.add('werewolf-portrait');
        } else if (character.toLowerCase().includes('seer') || character.toLowerCase().includes('seher')) {
            portrait.classList.add('seer-portrait');
        }
        
        // Wrap in container if not already
        if (!element.classList.contains('player-list-item')) {
            element.classList.add('player-list-item');
        }
        
        // Add portrait before text
        element.insertBefore(portrait, element.firstChild);
    });
}

// Function to map PHP character constants to canvas character names
function mapCharacterName(phpCharacter) {
    const characterMap = {
        'Werwolf': 'werewolf',
        'Dorfbewohner': 'villager', 
        'Seher/in': 'seer',
        'Hexe/r': 'witch',
        'Jäger/in': 'hunter',
        'Amor': 'cupid',
        'Beschützer/in': 'bodyguard',
        'Bürgermeister': 'mayor'
    };
    
    return characterMap[phpCharacter] || 'villager';
}

// Function to enhance player list with portraits (called from PHP)
function enhancePlayerList(playerData) {
    if (!window.werewolfCanvas || !playerData) return;
    
    playerData.forEach(player => {
        const playerElement = document.querySelector(`[data-player-id="${player.id}"]`);
        if (playerElement && player.character) {
            const character = mapCharacterName(player.character);
            
            // Create portrait
            const portrait = document.createElement('canvas');
            portrait.width = 32;
            portrait.height = 32;
            portrait.className = 'player-portrait';
            
            const ctx = portrait.getContext('2d');
            window.werewolfCanvas.canvas = portrait;
            window.werewolfCanvas.ctx = ctx;
            window.werewolfCanvas.createCharacterPortrait(character, 32);
            
            // Add character-specific animation class
            if (character === 'werewolf') {
                portrait.classList.add('werewolf-portrait');
            } else if (character === 'seer') {
                portrait.classList.add('seer-portrait');
            }
            
            // Add to player element
            if (!playerElement.querySelector('.player-portrait')) {
                playerElement.insertBefore(portrait, playerElement.firstChild);
                playerElement.classList.add('player-list-item');
            }
        }
    });
}