<?php

abstract class CRM_Appearancemodifier_AbstractLayout
{
    /*
     * This function returns an array of filepaths.
     */
    abstract public function getStyleSheets(): array;
    /*
     * This function has to modify the html.
     */
    abstract public function alterContent(&$content): void;
    /*
     * This function returns a classname that will applied on the container item.
     * It is used for writing the custom css rules.
     */
    public function className(): string
    {
        return 'appearancemodifier-';
    }
}
