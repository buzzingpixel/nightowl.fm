import crypto from 'crypto';
import fs from 'fs-extra';
import out from 'cli-output';
import path from 'path';
import recursive from 'recursive-readdir-sync';
import uglify from 'terser';

const appDir = process.cwd();
const jsLocation = `${appDir}/assets/js`;
const jsOutputDir = `${appDir}/public/assets/js`;
const manifestLocation = `${jsOutputDir}/manifest.json`;
const options = {
    sourceMap: true,
    mangle: true,
    compress: true,
    output: {
        beautify: false,
    },
};

// Process a single source JS file
export function processSourceFile (filePath, pathInsert) {
    const ext = path.extname(filePath);

    let relativePath = filePath.slice(
        filePath.indexOf(jsLocation) + 1 + jsLocation.length,
    );

    const relativePathInitial = relativePath;

    if (pathInsert) {
        relativePath = `${pathInsert}/${relativePath}`;
    }

    const outputFullPath = `${jsOutputDir}/${relativePath}`;
    const outputFullPathDir = path.dirname(outputFullPath);
    const sourceMapFullPath = `${outputFullPath}.map`;
    const sourceMapName = path.basename(sourceMapFullPath);
    const relativeReplacer = appDir + path.sep;
    const relativeName = filePath
        .slice(filePath.indexOf(relativeReplacer) + relativeReplacer.length)
        .split(path.sep)
        .join('/');
    const code = {};

    // Get the manifest
    const manifestObject = JSON.parse(fs.readFileSync(manifestLocation));

    // If the file no longer exists at the source, delete it
    if (!fs.existsSync(filePath)) {
        delete manifestObject[relativePathInitial];

        if (fs.existsSync(sourceMapFullPath)) {
            fs.removeSync(sourceMapFullPath);
        }

        if (fs.existsSync(outputFullPath)) {
            fs.removeSync(outputFullPath);
        }

        // Write the manifest output
        manifestObject[relativePathInitial] = relativePath;
        fs.writeFileSync(
            manifestLocation,
            JSON.stringify(manifestObject, null, 4),
        );

        return;
    }

    // If the file extension is not js, we should ignore it
    if (ext !== '.js') {
        return;
    }

    // Process the js content
    code[relativeName] = String(fs.readFileSync(filePath));
    const processed = uglify.minify(code, options);

    // If there's an error we need to let the user know and stop
    if (processed.error) {
        out.error('There was an error compiling Javascript');
        out.error(`Error: ${processed.error.message}`);
        out.error(`File: ${filePath}`);
        out.error(`Line: ${processed.error.line}`);
        out.error(`Column: ${processed.error.col}`);
        out.error(`Position: ${processed.error.pos}`);

        return;
    }

    // Create directory if it doesn't exist
    if (!fs.existsSync(outputFullPathDir)) {
        fs.mkdirSync(outputFullPathDir, { recursive: true });
    }

    // Write the sourcemap to disk
    fs.writeFileSync(sourceMapFullPath, processed.map);

    // Create the sourcemap code tag
    const sourceMapCode = `\n//# sourceMappingURL=${sourceMapName}`;

    // Write the JS file to disk
    fs.writeFileSync(outputFullPath, processed.code + sourceMapCode);

    // Write the manifest output
    manifestObject[relativePathInitial] = relativePath;
    fs.writeFileSync(
        manifestLocation,
        JSON.stringify(manifestObject, null, 4),
    );
}

export default (prod) => {
    // Let the user know what we're doing
    out.info('Compiling JS...');

    // Empty the JS dir first
    fs.emptyDirSync(jsOutputDir);

    // Add the gitignore file back
    fs.writeFileSync(
        `${jsOutputDir}/.gitignore`,
        '*\n!.gitignore',
    );

    // We'll create a hash of the JS to make a path insert for cache breaking
    // if we're in prod mode
    let pathInsert = '';

    if (prod === true) {
        let jsString = '';

        // Recursively iterate through the files in the JS location
        recursive(jsLocation).forEach((filePath) => {
            jsString += filePath + String(fs.readFileSync(filePath));
        });

        // Get a hash of the js content and set it to the pathInsert variable
        const md5 = crypto.createHash('md5');
        pathInsert = `${md5.update(jsString).digest('hex')}`;
    }

    // Create the manifest file
    fs.writeFileSync(
        manifestLocation,
        JSON.stringify({}, null, 4),
    );

    // Recursively iterate through the files in the JS location
    recursive(jsLocation).forEach((filePath) => {
        // Send the file for processing
        processSourceFile(filePath, pathInsert);
    });

    out.success('JS compiled');
};
