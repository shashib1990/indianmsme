import { createWorker } from 'tesseract.js';
import fs from 'fs';

async function runOcr(imagePath) {
    if (!fs.existsSync(imagePath)) {
        console.error(JSON.stringify({ success: false, error: 'File not found: ' + imagePath }));
        process.exit(1);
    }

    try {
        const worker = await createWorker('eng');
        const ret = await worker.recognize(imagePath);
        await worker.terminate();

        const rawText = ret.data.text || '';
        const confidence = ret.data.confidence || 0;

        // Extract lines with structural bounding box coordinates if available
        const lines = [];
        if (ret.data.lines && Array.isArray(ret.data.lines)) {
            for (const line of ret.data.lines) {
                const text = (line.text || '').trim();
                if (!text) continue;
                lines.push({
                    text: text,
                    confidence: line.confidence || 0,
                    bbox: line.bbox || null,
                    words: (line.words || []).map(w => ({
                        text: (w.text || '').trim(),
                        confidence: w.confidence || 0,
                        bbox: w.bbox || null
                    }))
                });
            }
        }

        const result = {
            success: true,
            text: rawText,
            confidence: confidence,
            lines: lines
        };

        console.log(JSON.stringify(result));
    } catch (err) {
        console.error(JSON.stringify({ success: false, error: err.message }));
        process.exit(1);
    }
}

const targetImage = process.argv[2];
if (!targetImage) {
    console.error(JSON.stringify({ success: false, error: 'Image path required' }));
    process.exit(1);
}

runOcr(targetImage);
