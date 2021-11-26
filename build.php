<?php

try {
    $phar = new Phar("./build/protoc-gen-hyperf.phar", 0, 'protoc-gen-hyperf.phar');
    $phar->startBuffering();
    $phar->buildFromIterator(
        new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS)),
        __DIR__
    );
    $stub = $phar->createDefaultStub("bin/protoc-gen-hyperf", "bin/protoc-gen-hyperf");
    $stub = "#!/usr/bin/env php\n" . $stub;
    $phar->compressFiles(Phar::GZ);
    $phar->setStub($stub);
    $phar->stopBuffering();
} catch (\Throwable $e) {
    echo $e->getMessage();
}
