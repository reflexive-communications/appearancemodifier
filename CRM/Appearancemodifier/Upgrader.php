<?php
use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Api4\UFGroup;
use Civi\Api4\Event;
use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\AppearancemodifierPetition;
use Civi\Api4\AppearancemodifierEvent;

/**
 * Collection of upgrade steps.
 */
class CRM_Appearancemodifier_Upgrader extends CRM_Appearancemodifier_Upgrader_Base
{
    // The limit parameter for the API calls.
    const QUERY_LIMIT = 25;
    /**
     * It creates the modified profile entry for the existing events.
     */
    private static function upgradeExistingEvents(): void
    {
        $limit = 25;
        $offset = 0;
        $currentNumberOfEvents = civicrm_api3('Event', 'getcount');
        while ($offset < $currentNumberOfEvents) {
            $events = Event::get(false)
                ->setLimit($limit)
                ->setOffset($offset)
                ->execute();
            foreach ($events as $event) {
                AppearancemodifierEvent::create(false)
                    ->addValue('event_id', $event['id'])
                    ->execute();
            }
            $offset = $offset + count($events);
        }
    }
    /**
     * It creates the modified profile entry for the existing petitions.
     * It uses API 3 as the surveys are not available in API 4.
     */
    private static function upgradeExistingPetitions(): void
    {
        $limit = 25;
        $offset = 0;
        $currentNumberOfPetitions = civicrm_api3('Survey', 'getcount', ['activity_type_id' => "Petition"]);
        while ($offset < $currentNumberOfPetitions) {
            $petitions = civicrm_api3('Survey', 'get', [
                'sequential' => 1,
                'activity_type_id' => "Petition",
                'options' => [
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ]);
            foreach ($petitions['values'] as $petition) {
                AppearancemodifierPetition::create(false)
                    ->addValue('survey_id', $petition['id'])
                    ->execute();
            }
            $offset = $offset + count($petitions['values']);
        }
    }
    /**
     * It creates the modified profile entry for the existing profiles.
     */
    private static function upgradeExistingProfiles(): void
    {
        $limit = 25;
        $offset = 0;
        $currentNumberOfUFGroups = civicrm_api3('UFGroup', 'getcount');
        while ($offset < $currentNumberOfUFGroups) {
            $ufGroups = UFGroup::get(false)
                ->setLimit($limit)
                ->setOffset($offset)
                ->execute();
            foreach ($ufGroups as $ufGroup) {
                AppearancemodifierProfile::create(false)
                    ->addValue('uf_group_id', $ufGroup['id'])
                    ->execute();
            }
            $offset = $offset + count($ufGroups);
        }
    }
    /**
     * It updates the modified profile entry for the existing profiles.
     * Set the values for each entity. On case of the invert consent field value
     * is true, set the 'invert' value, otherwise set 'default'.
     */
    public function upgradeExistingProfilesForBehaviour($offset): bool
    {
        $profiles = AppearancemodifierProfile::get(false)
            ->setLimit(self::QUERY_LIMIT)
            ->setOffset($offset)
            ->execute();
        foreach ($profiles as $profile) {
            $behaviour = (array_key_exists('invert_consent_fields', $profile) && $profile['invert_consent_fields'] !== null) ? 'invert' : 'default';
            AppearancemodifierProfile::update(false)
                ->addWhere('id', '=', $profile['id'])
                ->addValue('consent_field_behaviour', $behaviour)
                ->execute();
        }
        return true;
    }
    /**
     * It updates the modified petition entry for the existing petition.
     * Set the values for each entity. On case of the invert consent field value
     * is true, set the 'invert' value, otherwise set 'default'.
     */
    public function upgradeExistingPetitionsForBehaviour($offset): bool
    {
        $petitions = AppearancemodifierPetition::get(false)
            ->setLimit(self::QUERY_LIMIT)
            ->setOffset($offset)
            ->execute();
        foreach ($petitions as $petition) {
            $behaviour = (array_key_exists('invert_consent_fields', $petition) && $petition['invert_consent_fields'] !== null) ? 'invert' : 'default';
            AppearancemodifierPetition::update(false)
                ->addWhere('id', '=', $petition['id'])
                ->addValue('consent_field_behaviour', $behaviour)
                ->execute();
        }
        return true;
    }
    /**
     * It updates the modified event entry for the existing event.
     * Set the values for each entity. On case of the invert consent field value
     * is true, set the 'invert' value, otherwise set 'default'.
     */
    public function upgradeExistingEventsForBehaviour($offset): bool
    {
        $events = AppearancemodifierEvent::get(false)
            ->setLimit(self::QUERY_LIMIT)
            ->setOffset($offset)
            ->execute();
        foreach ($events as $event) {
            $behaviour = (array_key_exists('invert_consent_fields', $event) && $event['invert_consent_fields'] !== null) ? 'invert' : 'default';
            AppearancemodifierEvent::update(false)
                ->addWhere('id', '=', $event['id'])
                ->addValue('consent_field_behaviour', $behaviour)
                ->execute();
        }
        return true;
    }

    /**
     * Work with entities usually not available during the install step.
     *
     * This method can be used for any post-install tasks. For example, if a step
     * of your installation depends on accessing an entity that is itself
     * created during the installation (e.g., a setting or a managed entity), do
     * so here to avoid order of operation problems.
     */
    public function postInstall()
    {
        self::upgradeExistingProfiles();
        self::upgradeExistingPetitions();
        self::upgradeExistingEvents();
    }

    // By convention, functions that look like "function upgrade_NNNN()" are
    // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

    /**
     * Alter the table if necessary.
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_5300()
    {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_profile CHANGE outro additional_note text;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition CHANGE outro additional_note text;');
        return true;
    }
    /**
     * Alter the table if necessary.
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_5301()
    {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_profile ADD COLUMN font_color text;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition ADD COLUMN font_color text;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_event ADD COLUMN font_color text;');
        return true;
    }

    /**
     * Alter the table if necessary.
     * Add the signers_block_position column to the custom petitions table.
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_5302()
    {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition ADD COLUMN signers_block_position text;');
        return true;
    }
    /**
     * It creates the modified profile entry for the missing petitions.
     * #24
     * It uses API 3 as the surveys are not available in API 4.
     */
    public function upgrade_5303()
    {
        $limit = 25;
        $offset = 0;
        $currentNumberOfPetitions = civicrm_api3('Survey', 'getcount', ['activity_type_id' => "Petition"]);
        while ($offset < $currentNumberOfPetitions) {
            $petitions = civicrm_api3('Survey', 'get', [
                'sequential' => 1,
                'activity_type_id' => "Petition",
                'options' => [
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ]);
            foreach ($petitions['values'] as $petition) {
                $current = AppearancemodifierPetition::get(false)
                    ->addWhere('survey_id', '=', $petition['id'])
                    ->execute();
                if (count($current) > 0) {
                    continue;
                }
                AppearancemodifierPetition::create(false)
                    ->addValue('survey_id', $petition['id'])
                    ->execute();
            }
            $offset = $offset + count($petitions['values']);
        }
        return true;
    }

    /**
     * Alter the table if necessary.
     * Add the consent_field_behaviour column to the custom tables.
     * Set the values for each entity. On case of the invert consent field value
     * is true, set the 'invert' value, otherwise set 'default'.
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_5304()
    {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_profile ADD COLUMN consent_field_behaviour text;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition ADD COLUMN consent_field_behaviour text;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_event ADD COLUMN consent_field_behaviour text;');

        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_profile ADD COLUMN custom_settings text;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition ADD COLUMN custom_settings text;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_event ADD COLUMN custom_settings text;');
        $offset = 0;
        $currentNumber = count(AppearancemodifierProfile::get(false)
            ->selectRowCount()
            ->setLimit(1)
            ->execute());
        while ($offset < $currentNumber) {
            $this->addTask(E::ts('Process Profiles'), 'upgradeExistingProfilesForBehaviour', $offset);
            $offset = $offset + self::QUERY_LIMIT;
        }
        $offset = 0;
        $currentNumber = count(AppearancemodifierPetition::get(false)
            ->selectRowCount()
            ->setLimit(1)
            ->execute());
        while ($offset < $currentNumber) {
            $this->addTask(E::ts('Process Petitions'), 'upgradeExistingPetitionsForBehaviour', $offset);
            $offset = $offset + self::QUERY_LIMIT;
        }
        $offset = 0;
        $currentNumber = count(AppearancemodifierEvent::get(false)
            ->selectRowCount()
            ->setLimit(1)
            ->execute());
        while ($offset < $currentNumber) {
            $this->addTask(E::ts('Process Events'), 'upgradeExistingEventsForBehaviour', $offset);
            $offset = $offset + self::QUERY_LIMIT;
        }
        return true;
    }

    /**
     * Example: Run an external SQL script when the module is installed.
     */
   // public function install() {
   //   $this->executeSqlFile('sql/myinstall.sql');
   // }


  /**
   * Example: Run an external SQL script when the module is uninstalled.
   */
  // public function uninstall() {
  //  $this->executeSqlFile('sql/myuninstall.sql');
  // }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  // public function enable() {
  //  CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  // public function disable() {
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4200() {
  //   $this->ctx->log->info('Applying update 4200');
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
  //   CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
  //   return TRUE;
  // }


  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4201() {
  //   $this->ctx->log->info('Applying update 4201');
  //   // this path is relative to the extension base dir
  //   $this->executeSqlFile('sql/upgrade_4201.sql');
  //   return TRUE;
  // }


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4202() {
  //   $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

  //   $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
  //   $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
  //   $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
  //   return TRUE;
  // }
  // public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  // public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  // public function processPart3($arg5) { sleep(10); return TRUE; }

  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4203() {
  //   $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

  //   $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
  //   $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
  //   for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
  //     $endId = $startId + self::BATCH_SIZE - 1;
  //     $title = E::ts('Upgrade Batch (%1 => %2)', array(
  //       1 => $startId,
  //       2 => $endId,
  //     ));
  //     $sql = '
  //       UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
  //       WHERE id BETWEEN %1 and %2
  //     ';
  //     $params = array(
  //       1 => array($startId, 'Integer'),
  //       2 => array($endId, 'Integer'),
  //     );
  //     $this->addTask($title, 'executeSql', $sql, $params);
  //   }
  //   return TRUE;
  // }
}
