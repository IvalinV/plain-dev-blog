<?php

use Kudashevs\ShareButtons\Templaters\LaravelTemplater;

return [

    /*
    |--------------------------------------------------------------------------
    | Representation
    |--------------------------------------------------------------------------
    |
    | These values specify representations for different visual parts of buttons.
    |
    | Visual block/Visual container representation:
    | - 'block_prefix' represents a share buttons block start
    | - 'block_suffix' represents a share buttons block end
    | Each element representation:
    | - 'element_prefix' represents an element start
    | - 'element_suffix' represents an element end
    |
    */

    'block_prefix' => '<div id="social-buttons" class="flex items-center gap-x-5">',
    'block_suffix' => '</div>',
    'element_prefix' => '',
    'element_suffix' => '',

    /*
    |--------------------------------------------------------------------------
    | Share buttons
    |--------------------------------------------------------------------------
    |
    | These values specify configuration settings for each social media button.
    | The settings include a sharing url, a default text in the url, some extras.
    | The format of substitution depends on a templater (see Templaters section).
    | Note: It is allowed to provide a site's url to the copylink button, because
    | some people might want to see it there, even though using a hash is enough.
    |
    */

    'buttons' => [
        'bluesky' => [
            'url' => 'https://bsky.app/intent/compose?text=:url%20:text',
            'text' => 'Default share text',
        ],
        'copylink' => [
            'url' => ':url',
            'extra' => [
                'raw' => 'true',
                'hash' => 'true',
            ],
        ],
        'evernote' => [
            'url' => 'https://www.evernote.com/clip.action?url=:url&t=:text',
            'text' => 'Default share text',
        ],
        'facebook' => [
            'url' => 'https://www.facebook.com/sharer/sharer.php?u=:url&quote=:text',
            'text' => 'Default share text',
        ],
        'hackernews' => [
            'url' => 'https://news.ycombinator.com/submitlink?t=:text&u=:url',
            'text' => 'Default share text',
        ],
        'linkedin' => [
            'url' => 'https://www.linkedin.com/sharing/share-offsite?mini=true&url=:url&title=:text&summary=:summary',
            'text' => 'Default share text',
            'extra' => [
                'summary' => '',
            ],
        ],
        'mailto' => [
            'url' => 'mailto:?subject=:text&body=:url',
            'text' => 'Default share text',
        ],
        'mastodon' => [
            'url' => 'https://mastodon.social/share?text=:text&url=:url',
            'text' => 'Default share text',
        ],
        'pinterest' => [
            'url' => 'https://pinterest.com/pin/create/button/?url=:url',
        ],
        'pocket' => [
            'url' => 'https://getpocket.com/edit?url=:url&title=:text',
            'text' => 'Default share text',
        ],
        'reddit' => [
            'url' => 'https://www.reddit.com/submit?title=:text&url=:url',
            'text' => 'Default share text',
        ],
        'skype' => [
            'url' => 'https://web.skype.com/share?url=:url&text=:text&source=button',
            'text' => 'Default share text',
        ],
        'telegram' => [
            'url' => 'https://telegram.me/share/url?url=:url&text=:text',
            'text' => 'Default share text',
        ],
        'tumblr' => [
            'url' => 'https://www.tumblr.com/share?v=3&u=:url&t=:text',
            'text' => 'Default share text',
        ],
        'twitter' => [
            'url' => 'https://twitter.com/intent/tweet?text=:text&url=:url',
            'text' => 'Default share text',
        ],
        'vkontakte' => [
            'url' => 'https://vk.com/share.php?url=:url&title=:text',
            'text' => 'Default share text',
        ],
        'whatsapp' => [
            'url' => 'https://wa.me/?text=:url%20:text',
            'text' => 'Default share text',
        ],
        'xing' => [
            'url' => 'https://www.xing.com/spi/shares/new?url=:url',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    | These values specify link templates for each of the social media buttons.
    | The format of substitution depends on a templater (see Templaters section).
    | Note: Don't remove the social-button class from links because it's used in js.
    |
    */

    'templates' => [
        'bluesky' => '<a href=":url" class="social-button:class":id:title:rel><span class="fab fa-square-bluesky"></span></a>',
        'copylink' => '<a href=":url" class="social-button:class text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" id="clip":title:rel><span class="sr-only">Copy link</span><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-5 pointer-events-none"><path d="M19.902 4.098a3.75 3.75 0 0 0-5.304 0l-4.5 4.5a3.75 3.75 0 0 0 1.035 6.037.75.75 0 0 1-.646 1.353 5.25 5.25 0 0 1-1.449-8.45l4.5-4.5a5.25 5.25 0 1 1 7.424 7.424l-1.757 1.757a.75.75 0 1 1-1.06-1.06l1.757-1.757a3.75 3.75 0 0 0 0-5.304Zm-7.389 4.267a.75.75 0 0 1 1-.353 5.25 5.25 0 0 1 1.449 8.45l-4.5 4.5a5.25 5.25 0 1 1-7.424-7.424l1.757-1.757a.75.75 0 1 1 1.06 1.06l-1.757 1.757a3.75 3.75 0 1 0 5.304 5.304l4.5-4.5a3.75 3.75 0 0 0-1.035-6.037.75.75 0 0 1-.354-1Z"/></svg></a>',
        'evernote' => '<a href=":url" class="social-button:class":id:title:rel><span class="fab fa-evernote"></span></a>',
        'facebook' => '<a href=":url" class="social-button:class":id:title:rel><span class="fab fa-facebook-square"></span></a>',
        'hackernews' => '<a href=":url" class="social-button:class":id:title:rel><span class="fab fa-hacker-news"></span></a>',
        'linkedin' => '<a href=":url" class="social-button:class text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white":id:title:rel><span class="sr-only">LinkedIn</span><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-5 pointer-events-none"><path d="M20.447 20.452H16.89v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.346V9h3.414v1.561h.049c.476-.9 1.637-1.85 3.37-1.85 3.604 0 4.268 2.372 4.268 5.456v6.285ZM5.337 7.433a2.063 2.063 0 1 1 0-4.126 2.063 2.063 0 0 1 0 4.126ZM7.119 20.452H3.555V9h3.564v11.452ZM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003Z"/></svg></a>',
        'mailto' => '<a href=":url" class="social-button:class":id:title:rel><span class="fas fa-envelope"></span></a>',
        'mastodon' => '<a href=":url" class="social-button:class":id:title:rel><span class="fab fa-mastodon"></span></a>',
        'pinterest' => '<a href=":url" class="social-button:class":id:title:rel><span class="fab fa-pinterest"></span></a>',
        'pocket' => '<a href=":url" class="social-button:class":id:title:rel><span class="fab fa-get-pocket"></span></a>',
        'reddit' => '<a href=":url" class="social-button:class text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white":id:title:rel><span class="sr-only">Reddit</span><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-5 pointer-events-none"><path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-6.995 4.87-3.865 0-6.994-2.176-6.994-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/></svg></a>',
        'skype' => '<a href=":url" class="social-button:class":id:title:rel><span class="fab fa-skype"></span></a>',
        'telegram' => '<a href=":url" class="social-button:class":id:title:rel target="_blank"><span class="fab fa-telegram"></span></a>',
        'tumblr' => '<a href=":url" class="social-button:class":id:title:rel target="_blank"><span class="fab fa-square-tumblr"></span></a>',
        'twitter' => '<a href=":url" class="social-button:class text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white":id:title:rel><span class="sr-only">X</span><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-5 pointer-events-none"><path d="M13.6823 10.6218L20.2391 3H18.6854L12.9921 9.61788L8.44486 3H3.2002L10.0765 13.0074L3.2002 21H4.75404L10.7663 14.0113L15.5685 21H20.8131L13.6819 10.6218H13.6823ZM11.5541 13.0956L10.8574 12.0991L5.31391 4.16971H7.70053L12.1742 10.5689L12.8709 11.5655L18.6861 19.8835H16.2995L11.5541 13.096V13.0956Z"/></svg></a>',
        'vkontakte' => '<a href=":url" class="social-button:class":id:title:rel><span class="fab fa-vk"></span></a>',
        'whatsapp' => '<a href=":url" class="social-button:class":id:title:rel target="_blank"><span class="fab fa-square-whatsapp"></span></a>',
        'xing' => '<a href=":url" class="social-button:class":id:title:rel target="_blank"><span class="fab fa-square-xing"></span></a>',
    ],

    /*
    |--------------------------------------------------------------------------
    | Templaters
    |--------------------------------------------------------------------------
    |
    | This package uses a simple template engine to substitute values in different
    | configuration settings and templates. If you want to change the substitution
    | format, feel free to use your favorite template engine (in this case it is
    | recommended to introduce an adapter that conforms to the Templater interface).
    |
    */

    'templater' => LaravelTemplater::class,

    'url_templater' => LaravelTemplater::class,

];
