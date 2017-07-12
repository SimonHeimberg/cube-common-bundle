<?php

namespace CubeTools\CubeCommonBundle\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * API for keeping session data on success only.
 */
class KeepOnSuccess
{
    const STORAGE_KEY = 'cubetools_session_keep_on_sucess_only_keys';

    /**
     * Mark session data to keep on success only.
     *
     * @param SessionInterface $session
     * @param string[]         $keys    the key names in the session to mark
     */
    public static function markFor(SessionInterface $session, $keys)
    {
        foreach ($keys as $keyName) {
            $toCheck = $session->get(self::STORAGE_KEY, array());
            $toCheck[$keyName] = true;
            $session->set(self::STORAGE_KEY, $toCheck);
        }
    }
}
