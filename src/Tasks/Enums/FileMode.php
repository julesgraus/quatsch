<?php

namespace JulesGraus\Quatsch\Tasks\Enums;

enum FileMode: string
{
    /**
     * Open for reading only.
     * Place the file pointer at the beginning of the file.
     */
    case READ = 'r';

    /**
     * Open for reading and writing.
     * Place the file pointer at the beginning of the file.
     */
    case READ_WRITE = 'r+';

    /**
     * Open for writing only.
     * Place the file pointer at the beginning and truncate the file to zero length.
     * Create the file if it does not exist.
     */
    case WRITE_TRUNCATE = 'w';

    /**
     * Open for reading and writing.
     * Place the file pointer at the beginning and truncate the file to zero length.
     * Create the file if it does not exist.
     */
    case READ_WRITE_TRUNCATE = 'w+';

    /**
     * Open for writing only.
     * Place the file pointer at the end of the file (append mode).
     * Create the file if it does not exist.
     */
    case APPEND = 'a';

    /**
     * Open for reading and writing.
     * Place the file pointer at the end of the file (append mode).
     * Create the file if it does not exist.
     */
    case READ_APPEND = 'a+';

    /**
     * Create and open for writing only.
     * Place the file pointer at the beginning.
     * If the file already exists, the `fopen()` call will fail.
     */
    case EXCLUSIVE_CREATE = 'x';

    /**
     * Create and open for reading and writing.
     * Place the file pointer at the beginning.
     * If the file already exists, the `fopen()` call will fail.
     */
    case EXCLUSIVE_CREATE_READ = 'x+';

    /**
     * Open the file for writing only.
     * Place the file pointer at the end of the file.
     * Attempt to obtain an exclusive lock.
     * Create the file if it does not exist.
     */
    case APPEND_LOCK = 'c';

    /**
     * Open the file for reading and writing.
     * Place the file pointer at the beginning of the file.
     * Attempt to obtain an exclusive lock.
     * Create the file if it does not exist.
     */
    case READ_WRITE_LOCK = 'c+';
}
