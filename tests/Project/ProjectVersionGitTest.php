<?php

namespace Tests\CubeTools\CubeCommonBundle\Project;

use CubeTools\CubeCommonBundle\Project\ProjectVersionGit;

class ProjectVersionGitTest extends \PHPUnit_Framework_TestCase
{
    public function testInexistingDir()
    {
        $pv = new ProjectVersionGit(__DIR__.'/non_existing_dir/app');
        $v = $pv->getVersionString();
        $this->assertSame('', $v);
        $u = $pv->getVersionUrl();
        $this->assertSame('', $u);
    }

    public function testRealVersion()
    {
        $rootDir = __DIR__.'/..'; // %kernel.root_dir% normally
        $pv = new ProjectVersionGit($rootDir);
        $v = $pv->getVersionString();
        $this->assertNotEquals('', $v);
        $h = $pv->getGitHash();
        $this->assertRegexp('/^[a-z0-9]*$/', $h);
        $r = $pv->getGitRepoUrl();
        $u = $pv->getVersionUrl();
        if ('' === $r) {
            $this->markTestSkipped('origin url is not set');
        }
        $this->assertRegexp('|^http.*'.$h.'|', $u);
    }
}
