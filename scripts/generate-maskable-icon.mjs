// Script to generate a maskable PWA icon with proper safe area padding
// Maskable icons need 10% padding on each side (80% safe area)
import path from 'path';
import { fileURLToPath } from 'url';
import sharp from 'sharp';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '..');

const sizes = [192, 512];

async function generateMaskableIcon(size) {
    const padding = Math.round(size * 0.1); // 10% padding
    const iconSize = size - (padding * 2); // remaining space for icon

    // Create a white background with the icon centered
    const icon = await sharp(path.join(rootDir, 'public/images/simora_icon.png'))
        .resize(iconSize, iconSize, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
        .toBuffer();

    await sharp({
        create: {
            width: size,
            height: size,
            channels: 4,
            background: { r: 249, g: 115, b: 22, alpha: 1 } // #f97316 orange theme
        }
    })
        .composite([{ input: icon, top: padding, left: padding }])
        .png()
        .toFile(path.join(rootDir, `public/images/pwa-maskable-${size}x${size}.png`));

    console.log(`Generated: pwa-maskable-${size}x${size}.png`);
}

async function generateAppleTouchIcon() {
    // Apple touch icon should be 180x180
    await sharp(path.join(rootDir, 'public/images/simora_icon.png'))
        .resize(180, 180, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
        .png()
        .toFile(path.join(rootDir, 'public/apple-touch-icon-180x180.png'));

    console.log('Generated: apple-touch-icon-180x180.png');
}

for (const size of sizes) {
    await generateMaskableIcon(size);
}

await generateAppleTouchIcon();
console.log('All maskable icons generated!');
