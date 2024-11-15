<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;
use Config\Database;

class ImageService
{
    private ImageManager $manager;
    /** @var array<string> */
    private array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private int $maxFileSize = 5242880; // 5MB
    private \PDO $db;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * @param array{
     *     name: string,
     *     type: string,
     *     tmp_name: string,
     *     error: int,
     *     size: int
     * } $file
     */
    public function handleLogoUpload(array $file, int $enterpriseId): bool
    {
        try {
            $this->validateUpload($file);
            return $this->processAndSaveImage($file, $enterpriseId);
        } catch (Exception $e) {
            error_log("Erreur lors du traitement de l'image: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array{
     *     name: string,
     *     type: string,
     *     tmp_name: string,
     *     error: int,
     *     size: int
     * } $file
     */
    private function processAndSaveImage(array $file, int $enterpriseId): bool
    {
        try {
            $image = $this->manager->read($file['tmp_name']);
            $image->scale(width: 300, height: 300);

            // Convertir l'image en WebP et la récupérer comme chaîne de caractères
            $imageData = (string) $image->toWebp(quality: 90);

            // Mettre à jour la base de données
            $stmt = $this->db->prepare(
                "UPDATE Entreprises 
                SET logo = :logo 
                WHERE id = :id"
            );

            return $stmt->execute([
                ':logo' => $imageData,
                ':id' => $enterpriseId
            ]);
        } catch (Exception $e) {
            error_log("Erreur lors de la sauvegarde de l'image: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array{
     *     name: string,
     *     type: string,
     *     tmp_name: string,
     *     error: int,
     *     size: int
     * } $file
     * @throws Exception
     */
    private function validateUpload(array $file): void
    {
        // Vérifie si le fichier est vide
        if (empty($file['tmp_name'])) {
            throw new Exception("Aucun fichier n'a été uploadé");
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erreur lors de l'upload: " . $file['error']);
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new Exception("Le fichier est trop volumineux");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new Exception("Impossible d'ouvrir fileinfo");
        }

        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            throw new Exception("Type de fichier non autorisé");
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Fichier non uploadé via HTTP POST");
        }
    }

    public function deleteLogo(int $enterpriseId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE Entreprises 
                SET logo = NULL 
                WHERE id = :id"
            );
            return $stmt->execute([':id' => $enterpriseId]);
        } catch (Exception $e) {
            error_log("Erreur lors de la suppression du logo: " . $e->getMessage());
            return false;
        }
    }

    // Pour l'affichage de l'image
    public function getLogo(int $enterpriseId): void
    {
        $stmt = $this->db->prepare(
            "SELECT logo 
            FROM Entreprises 
            WHERE id = :id"
        );
        $stmt->execute([':id' => $enterpriseId]);
        $result = $stmt->fetch();

        if ($result && $result['logo']) {
            header('Content-Type: image/webp');
            echo $result['logo'];
            exit;
        }

        header('HTTP/1.0 404 Not Found');
        exit;
    }

    /**
     * Traite une image uploadée et la retourne au format WebP
     * @param array{
     *     name: string,
     *     type: string,
     *     tmp_name: string,
     *     error: int,
     *     size: int
     * } $file Le fichier uploadé ($_FILES['logo'])
     * @return string|null Les données de l'image en WebP ou null en cas d'erreur
     */
    public function processUploadedImage(array $file): ?string
    {
        try {
            $this->validateUpload($file);

            $image = $this->manager->read($file['tmp_name']);
            $image->scale(width: 300, height: 300);

            // Convertir l'image en WebP et la récupérer comme chaîne de caractères
            return (string) $image->toWebp(quality: 90);
        } catch (Exception $e) {
            error_log("Erreur lors du traitement de l'image: " . $e->getMessage());
            return null;
        }
    }

    public function resizeImage(string $imageData, int $maxWidth, int $maxHeight): string
    {
        $image = imagecreatefromstring($imageData);
        if ($image === false) {
            throw new Exception("Impossible de créer l'image à partir des données");
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Calcul des nouvelles dimensions en conservant le ratio
        $ratio = min($maxWidth / $width, $maxHeight / $height);

        if ($ratio >= 1) {
            return $imageData; // Pas besoin de redimensionner si l'image est plus petite
        }

        $newWidth = max(1, (int)round($width * $ratio));
        $newHeight = max(1, (int)round($height * $ratio));

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        if ($newImage === false) {
            throw new Exception("Impossible de créer la nouvelle image");
        }

        // Préserver la transparence pour les PNG
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        $success = imagewebp($newImage, null, 80);
        $resizedImage = ob_get_clean();

        if ($success === false || $resizedImage === false) {
            throw new Exception("Échec de la conversion en WebP");
        }

        imagedestroy($image);
        imagedestroy($newImage);

        return $resizedImage;
    }
}
