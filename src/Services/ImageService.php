<?php
namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class ImageService {
    private ImageManager $manager;
    
    /** @var array<int, string> */
    private array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    
    private int $maxFileSize = 5242880; // 5MB
    private string $uploadDir;
    
    public function __construct() {
        $this->manager = new ImageManager(new Driver());
        $this->uploadDir = PUBLIC_PATH . '/assets/images/logos';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Gère l'upload et le traitement d'un logo
     * @param array{
     *     name: string,
     *     type: string,
     *     tmp_name: string,
     *     error: int,
     *     size: int
     * } $file Fichier uploadé ($_FILES['logo'])
     * @param int $enterpriseId ID de l'entreprise
     * @return bool Succès de l'opération
     */
    public function handleLogoUpload(array $file, int $enterpriseId): bool {
        try {
            $this->validateUpload($file);
            
            $image = $this->manager->read($file['tmp_name']);
            
            // Redimensionnement avec conservation du ratio
            $image->scale(width: 300, height: 300);
            
            // Sauvegarde en WebP avec l'ID de l'entreprise
            $filepath = $this->uploadDir . '/' . $enterpriseId . '.webp';
            
            // Conversion et sauvegarde en WebP
            $image->toWebp(quality: 90)->save($filepath);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur lors du traitement de l'image: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valide le fichier uploadé
     * @param array{
     *     name: string,
     *     type: string,
     *     tmp_name: string,
     *     error: int,
     *     size: int
     * } $file
     * @throws Exception
     */
    private function validateUpload(array $file): void {
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
     * Supprime le logo d'une entreprise
     * @param int $enterpriseId
     * @return bool
     */
    public function deleteLogo(int $enterpriseId): bool {
        $filepath = $this->uploadDir . '/' . $enterpriseId . '.webp';
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
}