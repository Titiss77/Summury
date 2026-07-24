<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Jonathan Vollebregt (jnvsor@gmail.com), Rokas Šleinius (raveren@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Kint\Value\Representation;

use Kint\Utils;
use SplFileInfo;

class SplFileInfoRepresentation extends StringRepresentation
{
    public function __construct(\SplFileInfo $fileInfo)
    {
        $path = $fileInfo->getPathname();

        $perms = 0;
        $owner = null;
        $group = null;
        $mtime = null;
        $realpath = null;
        $linktarget = null;
        $size = null;
        $is_file = false;
        $is_dir = false;
        $is_link = false;
        $typename = 'Unknown file';

        try {
            // SplFileInfo::getRealPath will return cwd when path is ''
            if ('' !== $path && $fileInfo->getRealPath()) {
                $perms = $fileInfo->getPerms();
                $size = $fileInfo->getSize();
                $owner = $fileInfo->getOwner();
                $group = $fileInfo->getGroup();
                $mtime = $fileInfo->getMTime();
                $realpath = $fileInfo->getRealPath();
            }

            $is_dir = $fileInfo->isDir();
            $is_file = $fileInfo->isFile();
            $is_link = $fileInfo->isLink();

            if ($is_link) {
                $lt = $fileInfo->getLinkTarget();
                $linktarget = false === $lt ? null : $lt;
            }
        } catch (\RuntimeException $e) {
            if (!\str_contains($e->getMessage(), ' open_basedir ')) {
                throw $e; // @codeCoverageIgnore
            }
        }

        $typeflag = '-';

        switch ($perms & 0xF000) {
            case 0xC000:
                $typename = 'Socket';
                $typeflag = 's';

                break;

            case 0x6000:
                $typename = 'Block device';
                $typeflag = 'b';

                break;

            case 0x2000:
                $typename = 'Character device';
                $typeflag = 'c';

                break;

            case 0x1000:
                $typename = 'Named pipe';
                $typeflag = 'p';

                break;

            default:
                if ($is_file) {
                    if ($is_link) {
                        $typename = 'File symlink';
                        $typeflag = 'l';
                    } else {
                        $typename = 'File';
                        $typeflag = '-';
                    }
                } elseif ($is_dir) {
                    if ($is_link) {
                        $typename = 'Directory symlink';
                        $typeflag = 'l';
                    } else {
                        $typename = 'Directory';
                        $typeflag = 'd';
                    }
                }

                break;
        }

        $flags = [$typeflag];

        // User
        $flags[] = (($perms & 0o400) ? 'r' : '-');
        $flags[] = (($perms & 0o200) ? 'w' : '-');
        if ($perms & 0o100) {
            $flags[] = ($perms & 0o4000) ? 's' : 'x';
        } else {
            $flags[] = ($perms & 0o4000) ? 'S' : '-';
        }

        // Group
        $flags[] = (($perms & 0o040) ? 'r' : '-');
        $flags[] = (($perms & 0o020) ? 'w' : '-');
        if ($perms & 0o010) {
            $flags[] = ($perms & 0o2000) ? 's' : 'x';
        } else {
            $flags[] = ($perms & 0o2000) ? 'S' : '-';
        }

        // Other
        $flags[] = (($perms & 0o004) ? 'r' : '-');
        $flags[] = (($perms & 0o002) ? 'w' : '-');
        if ($perms & 0o001) {
            $flags[] = ($perms & 0o1000) ? 's' : 'x';
        } else {
            $flags[] = ($perms & 0o1000) ? 'S' : '-';
        }

        $contents = \implode('', $flags).' '.$owner.' '.$group.' '.$size.' ';

        if (null !== $mtime) {
            if (\date('Y', $mtime) === \date('Y')) {
                $contents .= \date('M d H:i', $mtime);
            } else {
                $contents .= \date('M d Y', $mtime);
            }
        }

        $contents .= ' ';

        if ($is_link && null !== $linktarget) {
            $contents .= $path.' -> '.$linktarget;
        } elseif (null !== $realpath && \strlen($realpath) < \strlen($path)) {
            $contents .= $realpath;
        } else {
            $contents .= $path;
        }

        $label = $typename;

        if (null !== $size && $is_file) {
            $size = Utils::getHumanReadableBytes($size);
            $label .= ' ('.$size['value'].$size['unit'].')';
        }

        parent::__construct($label, $contents, 'splfileinfo');
    }

    public function getHint(): string
    {
        return 'splfileinfo';
    }
}
