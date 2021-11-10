<?php
/**
 * This file is part of Crawler Detect - the web crawler detection library.
 * @author (c) Mark Beech <m@rkbee.ch>
 * @copyright (c) Mark Beech <m@rkbee.ch>
 * @license MIT License
 */

require_once dirname(__FILE__).'/../../../thirdparty/CrawlerDetect/Fixtures/AbstractProvider.php';

class Headers extends AbstractProvider
{
    /**
     * All possible HTTP headers that represent the user agent string.
     *
     * @var array
     */
    protected $data = array(
        // The default User-Agent string.
        'HTTP_USER_AGENT',
        // Header can occur on devices using Opera Mini.
        'HTTP_X_OPERAMINI_PHONE_UA',
        // Vodafone specific header: http://www.seoprinciple.com/mobile-web-community-still-angry-at-vodafone/24/
        'HTTP_X_DEVICE_USER_AGENT',
        'HTTP_X_ORIGINAL_USER_AGENT',
        'HTTP_X_SKYFIRE_PHONE',
        'HTTP_X_BOLT_PHONE_UA',
        'HTTP_DEVICE_STOCK_UA',
        'HTTP_X_UCBROWSER_DEVICE_UA',
        // Sometimes, bots (especially Google) use a genuine user agent, but fill this header in with their email address
        'HTTP_FROM',
        'HTTP_X_SCANNER', // Seen in use by Netsparker
    );
}
