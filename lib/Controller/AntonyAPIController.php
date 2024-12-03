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

	public function version() : DataResponse {
		return new DataResponse([
			'Version' => 2
		], Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string|null $projectFolder
	 * @param string|null $user
	 * @return DataResponse
	 */
	public function checkDeleteProjectFolder(?string $projectFolder = null, ?string $user): DataResponse {
		if ($projectFolder == null)
			return new DataResponse(['error' => "Parameters missing"], Http::STATUS_BAD_REQUEST);


		$usersRootFolder = $this->rootFolder->getUserFolder($user);
		try
		{
			$sharedFolder = $usersRootFolder->get($projectFolder);
			$contents = $sharedFolder->getDirectoryListing();

			$removed = false;
			if (empty($contents))
			{
				$sharedFolder->delete();
				$removed = true;
			}

			return new DataResponse([
				'Removed' => $removed
			], Http::STATUS_OK);
		} catch (Exception | Throwable $e)
		{
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string@null $fullShareId
	 * @param string|null $projectFolder
	 * @return DataResponse
	 */
	public function renameShareFolder(?string $fullShareId, ?string $projectFolder = null): DataResponse {
		if ($fullShareId == null || $projectFolder == null)
			return new DataResponse(['error' => "Parameters missing"], Http::STATUS_BAD_REQUEST);
		
		try {
			$share = $this->shareManager->getShareById($fullShareId);
			$user = $share->getSharedWith();

			$usersRootFolder = $this->rootFolder->getUserFolder($user);
			try
			{
				$usersRootFolder->get($projectFolder);
			} catch (Exception | Throwable $e)
			{
				$usersRootFolder->newFolder($projectFolder);
			}
			
			$currentPath = $share->getTarget();
			$newPath = "/" . $projectFolder . "/" . trim($share->getNode()->getName(), "/");			
			
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
