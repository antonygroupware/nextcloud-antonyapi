<?php
namespace OCA\AntonyApi\Controller;

use Exception;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;
use Throwable;
use OCP\Share\IManager as ShareManager;
use OCP\Files\IRootFolder;

class AntonyAPIController extends OCSController {

        public function __construct(
                string             $appName,
                IRequest           $request,
                protected ShareManager $shareManager,
                protected IRootFolder $rootFolder,
                private ?string    $userId
        ) {
                parent::__construct($appName, $request);
        }

        /**
         * @NoAdminRequired
         *
         * @param string@null $fullShareId
         * @param string|null $prefix
         * @return DataResponse
         */
        public function renameShareFolder(?string $fullShareId, ?string $prefix = null): DataResponse {
                if ($fullShareId == null || $prefix == null)
                        return new DataResponse(['error' => "Parameters missing"], Http::STATUS_BAD_REQUEST);

                try {
                        $share = $this->shareManager->getShareById($fullShareId);
                        $user = $share->getSharedWith();

                        $usersRootFolder = $this->rootFolder->getUserFolder($user);
                        try
                        {
                                $usersRootFolder->get($prefix);
                        } catch (Exception | Throwable $e)
                        {
                                $usersRootFolder->newFolder($prefix);
                        }

                        $currentPath = $share->getTarget();
                        $newPath = "/" . $prefix . "/" . trim($share->getNode()->getName(), "/");

                        $share->setTarget($newPath);
                        $share = $this->shareManager->moveShare($share, $share->getSharedWith());

                        return new DataResponse([
                                'success' => true,
                                'newPath' => $newPath,
                                'user' => $user
                        ], Http::STATUS_OK);

                } catch (Exception | Throwable $e) {
                        return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
                }
        }


}
?>