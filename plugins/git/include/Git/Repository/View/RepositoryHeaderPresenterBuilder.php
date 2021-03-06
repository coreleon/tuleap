<?php
/**
 * Copyright (c) Enalean, 2018. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Git\Repository\View;

use Git_Driver_Gerrit_GerritDriverFactory;
use Git_Driver_Gerrit_ProjectCreatorStatus;
use Git_Driver_Gerrit_UserAccountManager;
use Git_GitRepositoryUrlManager;
use Git_RemoteServer_GerritServer;
use GitPermissionsManager;
use GitRepository;
use PFUser;

class RepositoryHeaderPresenterBuilder
{
    /**
     * @var Git_GitRepositoryUrlManager
     */
    private $url_manager;

    /**
     * @var Git_Driver_Gerrit_GerritDriverFactory
     */
    private $driver_factory;

    /**
     * @var Git_Driver_Gerrit_UserAccountManager
     */
    private $gerrit_usermanager;

    /**
     * @var Git_RemoteServer_GerritServer[]
     */
    private $gerrit_servers;

    /**
     * @var GitPermissionsManager
     */
    private $permissions_manager;

    /**
     * @var Git_Driver_Gerrit_ProjectCreatorStatus
     */
    private $project_creator_status;

    public function __construct(
        Git_GitRepositoryUrlManager $url_manager,
        Git_Driver_Gerrit_GerritDriverFactory $driver_factory,
        Git_Driver_Gerrit_ProjectCreatorStatus $project_creator_status,
        Git_Driver_Gerrit_UserAccountManager $gerrit_usermanager,
        GitPermissionsManager $permissions_manager,
        array $gerrit_servers
    ) {
        $this->url_manager            = $url_manager;
        $this->driver_factory         = $driver_factory;
        $this->project_creator_status = $project_creator_status;
        $this->gerrit_usermanager     = $gerrit_usermanager;
        $this->permissions_manager    = $permissions_manager;
        $this->gerrit_servers         = $gerrit_servers;
    }

    /** @return RepositoryHeaderPresenter */
    public function build(GitRepository $repository, PFUser $current_user)
    {
        $parent_repository_presenter = null;
        $parent_repository = $repository->getParent();
        if (! empty($parent_repository)) {
            $parent_repository_presenter = $this->buildParentPresenter($parent_repository);
        }

        $gerrit_status_presenter = $this->buildGerritStatusPresenter($repository);
        $clone_presenter         = $this->buildClonePresenter($repository, $current_user);

        $is_admin = $this->permissions_manager->userIsGitAdmin($current_user, $repository->getProject()) ||
            $repository->belongsTo($current_user);

        $admin_url = $this->url_manager->getRepositoryAdminUrl($repository);

        return new RepositoryHeaderPresenter(
            $repository,
            $is_admin,
            $admin_url,
            $clone_presenter,
            $gerrit_status_presenter,
            $parent_repository_presenter
        );
    }

    private function buildParentPresenter(GitRepository $parent_repository)
    {
        return new ParentRepositoryPresenter(
            $parent_repository,
            $this->url_manager->getRepositoryBaseUrl($parent_repository)
        );
    }

    private function buildGerritStatusPresenter(GitRepository $repository)
    {
        return new GerritStatusPresenter(
            $repository,
            $this->project_creator_status,
            $this->driver_factory,
            $this->gerrit_servers
        );
    }

    private function buildClonePresenter(GitRepository $repository, PFUser $current_user)
    {
        $access_urls = $repository->getAccessURL();
        $clone_urls = new CloneURLs();
        if (isset($access_urls['ssh'])) {
            $clone_urls->setSshUrl($access_urls['ssh']);
        }
        if (isset($access_urls['http'])) {
            $clone_urls->setHttpsUrl($access_urls['http']);
        }
        if ($repository->isMigratedToGerrit()) {
            $gerrit_user    = $this->gerrit_usermanager->getGerritUser($current_user);
            $gerrit_server  = $this->gerrit_servers[$repository->getRemoteServerId()];
            $driver         = $this->driver_factory->getDriver($gerrit_server);
            $gerrit_project = $driver->getGerritProjectName($repository);

            $clone_url = $gerrit_server->getEndUserCloneUrl($gerrit_project, $gerrit_user);
            $clone_urls->setGerritUrl($clone_url);
        }
        return new ClonePresenter($clone_urls);
    }
}
