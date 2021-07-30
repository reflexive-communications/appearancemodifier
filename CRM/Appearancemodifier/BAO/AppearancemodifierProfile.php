<?php
use CRM_Appearancemodifier_ExtensionUtil as E;

class CRM_Appearancemodifier_BAO_AppearancemodifierProfile extends CRM_Appearancemodifier_DAO_AppearancemodifierProfile
{

  /**
   * Create a new AppearancemodifierProfile based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Appearancemodifier_DAO_AppearancemodifierProfile|NULL
   *
  public static function create($params) {
    $className = 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile';
    $entityName = 'AppearancemodifierProfile';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */
}
