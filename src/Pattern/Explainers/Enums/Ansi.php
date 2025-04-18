<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Pattern\Explainers\Enums;

enum Ansi: string
{
    case RESET       = "\033[0m";
    case BOLD        = "\033[1m";
    case DIM         = "\033[2m";
    case ITALIC      = "\033[3m";
    case UNDERLINE   = "\033[4m";
    case BLINK       = "\033[5m";
    case REVERSE     = "\033[7m";
    case HIDDEN      = "\033[8m";
    case STRIKETHROUGH = "\033[9m";


    // Foreground colors
    case BLACK       = "\033[30m";
    case RED         = "\033[31m";
    case GREEN       = "\033[32m";
    case YELLOW      = "\033[33m";
    case BLUE        = "\033[34m";
    case MAGENTA     = "\033[35m";
    case CYAN        = "\033[36m";
    case WHITE       = "\033[37m";

    // Bright foreground colors
    case BRIGHT_BLACK   = "\033[90m";
    case BRIGHT_RED     = "\033[91m";
    case BRIGHT_GREEN   = "\033[92m";
    case BRIGHT_YELLOW  = "\033[93m";
    case BRIGHT_BLUE    = "\033[94m";
    case BRIGHT_MAGENTA = "\033[95m";
    case BRIGHT_CYAN    = "\033[96m";
    case BRIGHT_WHITE   = "\033[97m";

    // Background colors
    case BG_BLACK     = "\033[40m";
    case BG_RED       = "\033[41m";
    case BG_GREEN     = "\033[42m";
    case BG_YELLOW    = "\033[43m";
    case BG_BLUE      = "\033[44m";
    case BG_MAGENTA   = "\033[45m";
    case BG_CYAN      = "\033[46m";
    case BG_WHITE     = "\033[47m";

    // Bright background colors
    case BG_BRIGHT_BLACK   = "\033[100m";
    case BG_BRIGHT_RED     = "\033[101m";
    case BG_BRIGHT_GREEN   = "\033[102m";
    case BG_BRIGHT_YELLOW  = "\033[103m";
    case BG_BRIGHT_BLUE    = "\033[104m";
    case BG_BRIGHT_MAGENTA = "\033[105m";
    case BG_BRIGHT_CYAN    = "\033[106m";
    case BG_BRIGHT_WHITE   = "\033[107m";
}

