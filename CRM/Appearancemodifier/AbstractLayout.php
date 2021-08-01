<?php

abstract class CRM_Appearancemodifier_AbstractLayout
{
    /*
     * This function has to set the style resources if the layout needs it.
     */
    abstract public function setStyleSheets(): void;
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
