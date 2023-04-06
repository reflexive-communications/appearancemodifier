<?php

namespace Civi\Appearancemodifier;

abstract class AbstractLayout
{
    protected string $currentClassName;

    /**
     * @param string $currentClassName
     */
    public function __construct(string $currentClassName)
    {
        $this->currentClassName = $currentClassName;
    }

    /**
     * This function has to set the style resources if the layout needs it.
     */
    abstract public function setStyleSheets(): void;

    /**
     * This function has to modify the html.
     *
     * @param $content
     */
    abstract public function alterContent(&$content): void;

    /**
     * This function returns a classname that will applied on the container item.
     * It is used for writing the custom css rules.
     *
     * @return string
     */
    public function className(): string
    {
        return 'appearancemodifier-';
    }
}
