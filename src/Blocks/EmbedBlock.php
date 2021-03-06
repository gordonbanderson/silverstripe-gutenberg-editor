<?php

namespace MadeHQ\Gutenberg\Blocks;

class EmbedBlock extends BaseBlock
{
    /**
     * @config
     * @var int
     */
    private static $width = 560;

    /**
     * @config
     * @var int
     */
    private static $height = 315;

    /**
     * @param string $content
     * @param array $attributes
     * @return string
     */
    public function render($content, array $attributes = array())
    {
        $width = sprintf('width="%s"', static::config()->get('width'));
        $height = sprintf('height="%s"', static::config()->get('height'));

        if (array_key_exists('html', $attributes)) {
            $markup = $attributes['html'];

            return preg_replace(['/width="(\w+)"/', '/height="(\w+)"/'], [$width, $height], $markup);
        }

        return $content;
    }
}
