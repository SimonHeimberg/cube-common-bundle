<?php

namespace CubeTools\CubeCommonBundle\Project;

/**
 * Class to read version from this projects git repository.
 */
class ProjectVersionGit
{
    private $kernelRoot;
    private $data = null;

    /**
     * Create service.
     *
     * @param string $kernelRoot kernel root directory, git repo is in its parent directory
     */
    public function __construct($kernelRoot)
    {
        $this->kernelRoot = $kernelRoot;
    }

    /**
     * @return string version of the project, read from git
     */
    public function getVersionString()
    {
        return $this->getGitData()['tag'];
    }

    /**
     * @return string id of the project version, git hash
     */
    public function getGitHash()
    {
        return $this->getGitData()['hash'];
    }

    /**
     * @return string url to the exact project version
     */
    public function getVersionUrl()
    {
        $repo = $this->getGitRepoUrl();
        if ($repo) {
            return $repo.'/commit/'.$this->getGitHash();
        }

        return '';
    }

    /**
     * @return string url to the project
     */
    public function getGitRepoUrl()
    {
        return str_replace('.git', '', $this->getGitData()['url']);
    }

    protected function getGitData()
    {
        if (null !== $this->data) {
            return $this->data;
        }

        $gitDir = $this->kernelRoot.'/../.git/';
        $headFile = $gitDir.'HEAD';
        if (is_readable($headFile)) {
            $data = $this->queryGitData();
        } else {
            $data = array('hash' => '', 'url' => '', 'tag' => '');
        }
        $this->data = $data;

        return $data;
    }

    protected function queryGitData()
    {
        $data = array();
        $data['hash'] = exec('git rev-parse HEAD');
        $data['url'] = exec('git config --get remote.origin.url');
        $data['tag'] = exec('git describe --tags --always');

        return $data;
    }
}
