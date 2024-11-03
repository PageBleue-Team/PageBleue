<?php
namespace App\Services;

// Inclusion du fichier de configuration
require_once __DIR__ . '/../../config/config.php';
define('MAX_IMAGE_SIZE', $_ENV['MAX_IMAGE_SIZE']);
define('UPLOAD_DIR', $_ENV['UPLOAD_DIR']);

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class ImageService {
    private ImageManager $manager;
    private array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private int $maxFileSize = 5242880; // 5MB
    
    public function __construct() {
        $this->manager = new ImageManager(new Driver());
    }
    
    /**
     * Gère l'upload et le traitement d'un logo
     * @param array $file Fichier uploadé ($_FILES['logo'])
     * @return string|null Chemin du fichier traité ou null en cas d'erreur
     */
    public function handleLogoUpload(array $file): ?string {
        try {
            $this->validateUpload($file);
            
            $image = $this->manager->read($file['tmp_name']);
            
            // Redimensionnement avec conservation du ratio
            $image->scale(width: MAX_IMAGE_SIZE, height: MAX_IMAGE_SIZE);
            
            // Génération du nom de fichier unique
            $filename = uniqid('logo_') . '.webp';
            $filepath = UPLOAD_DIR . '/' . $filename;
            
            // Conversion et sauvegarde en WebP
            $image->toWebp(quality: 90)->save($filepath);
            
            return $filename;
            
        } catch (Exception $e) {
            error_log("Erreur lors du traitement de l'image: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Valide le fichier uploadé
     * @param array $file
     * @throws Exception
     */
    private function validateUpload(array $file): void {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new Exception("Aucun fichier n'a été uploadé");
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erreur lors de l'upload: " . $file['error']);
        }
        
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception("Le fichier est trop volumineux");
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            throw new Exception("Type de fichier non autorisé");
        }
        
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Fichier non uploadé via HTTP POST");
        }
    }
    
    /**
     * Supprime un logo
     * @param string $filename
     * @return bool
     */
    public function deleteLogo(string $filename): bool {
        $filepath = UPLOAD_DIR . '/' . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
}