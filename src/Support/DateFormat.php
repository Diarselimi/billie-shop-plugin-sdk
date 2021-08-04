<?php

declare(strict_types=1);

namespace App\Support;

use Ozean12\Support\Formatting\DateFormat as SupportDateFormat;

/**
 * @deprecated
 * @see \Ozean12\Support\Formatting\DateFormat
 */
interface DateFormat extends SupportDateFormat
{
    /**
     * @deprecated This format is not compatible with ISO-8601, use ATOM instead
     * @since 7.2
     */
    public const ISO8601 = 'Y-m-d\TH:i:sO';

    /**
     * @deprecated Preferably, use FORMAT_YMD_HIS
     * @since 7.2
     */
    public const ATOM = 'Y-m-d\TH:i:sP';
}
