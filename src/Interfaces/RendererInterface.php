<?php

namespace Rougin\Slytherin\Interfaces;

/**
 * Renderer Interface
 *
 * An interface for handling third party template engines
 * 
 * @package Slytherin
 * @author  Rougin Royce Gutib <rougingutib@gmail.com>
 */
interface RendererInterface
{
    /**
     * Renders a template
     *
     * @param  string $template
     * @param  array  $data
     * @return string
     */
    public function render($template, $data = []);
}
