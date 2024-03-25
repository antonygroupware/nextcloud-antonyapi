<?php
namespace OCA\AntonyApi\Controller;

use Exception;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;
use Throwable;
use OCP\Share\IManager as ShareManager;

class AntonyAPIController extends OCSController {

	public function __construct(
		string             $appName,
		IRequest           $request,
		protected ShareManager $shareManager,
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
			
			if (!$this->canRenameShare($share)) {
				return new DataResponse(['error' => "Only users with permission can rename shares"], Http::STATUS_BAD_REQUEST);
			}			
			
			$currentPath = $share->getTarget();
			$newPath = "/" . $prefix . " " . trim($currentPath, "/");			
			
			$share->setTarget($newPath);
			$share = $this->shareManager->moveShare($share, $share->getSharedWith());
			
			return new DataResponse(['success' => true, 'newPath' => $newPath], Http::STATUS_OK);
		
		} catch (Exception | Throwable $e) {		
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}
	
	/**
	 * Does the user have permission the rename the share
	 *
	 * @param \OCP\Share\IShare $share the share to check
	 * @return boolean
	 */
	protected function canRenameShare(\OCP\Share\IShare $share): bool {
		// A file with permissions 0 can't be accessed by us. So Don't show it
		if ($share->getPermissions() === 0) {
			return false;
		}

		// if the user is the recipient, i can rename
		if ($share->getShareType() === \OCP\Share\IShare::TYPE_USER && $share->getSharedWith() === $this->userId)
		{			
			return true;
		}

		// The owner of the file and the creator of the share can always rename
		if ($share->getShareOwner() === $this->userId || $share->getSharedBy() === $this->userId) 
		{			
			return true;
		}

		return false;
	}	


}
