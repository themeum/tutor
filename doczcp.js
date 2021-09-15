const fs = require('fs');
const path = require('path');

function copyFileSync( source, target ) {

    var targetFile = target;

    // If target is a directory, a new file with the same name will be created
    if ( fs.existsSync( target ) ) {
        if ( fs.lstatSync( target ).isDirectory() ) {
            targetFile = path.join( target, path.basename( source ) );
        }
    }

    fs.writeFileSync(targetFile, fs.readFileSync(source));
}

function copyFolderRecursiveSync( source, target ) {
    var files = [];

    // Check if folder needs to be created or integrated
    var targetFolder = path.join( target, path.basename( source ) );
    if ( !fs.existsSync( targetFolder ) ) {
        fs.mkdirSync( targetFolder );
    }

    // Copy
    if ( fs.lstatSync( source ).isDirectory() ) {
        files = fs.readdirSync( source );
        files.forEach( function ( file ) {
            var curSource = path.join( source, file );
            if ( fs.lstatSync( curSource ).isDirectory() ) {
                copyFolderRecursiveSync( curSource, targetFolder );
            } else {
                copyFileSync( curSource, targetFolder );
            }
        } );
    }
}

const dir = path.resolve(__dirname+'/.docz/src');
const src_dir = path.resolve(__dirname+'/v2-library/src/gatsby-theme-docz');

if(!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
    console.log('Directory Created', dir);
}

if(!fs.existsSync(dir+'/gatsby-theme-docz')) {
    copyFolderRecursiveSync(src_dir, dir);
    console.log('Copied gatsby theme', src_dir, dir);
}