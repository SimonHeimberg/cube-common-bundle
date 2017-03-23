<?php

namespace CubeTools\CubeCommonBundle\Settings;

interface ColumnSettingsInterface
{
    /**
     * Get column settings (one table) for the user.
     *
     * @param string $saveId id to identify the settings
     *
     * @return any|null the saved column settings or null
     *
     * @throws \Exception any exception on failure
     */
    public function getColSettings($saveId);

    /**
     * Set column settings (one table) for the user.
     *
     * @param string $saveId   id to identify the settings
     * @param any    $settings column settings to save
     *
     * @throws \Exception any exception on failure
     */
    public function setColSettings($saveId, $settings);
}
