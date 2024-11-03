<?php
// src/Controller/PageController.php

namespace App\Controller;

use App\Repository\EntrepriseRepository;
use App\Exception\DatabaseException;

class PageController
{
    private $entrepriseRepository;
    private $siteConfig;

    public function __construct(EntrepriseRepository $entrepriseRepository, array $siteConfig)
    {
        $this->entrepriseRepository = $entrepriseRepository;
        $this->siteConfig = $siteConfig;
    }

    public function home(): void
    {
        $viewData = [
            'dbError' => false,
            'errorMessage' => '',
            'metaTitle' => $this->siteConfig['meta']['title'] ?? 'Page Bleue',
            'metaDescription' => $this->siteConfig['meta']['description'] ?? 'Annuaire d\'entreprises',
            'googleVerification' => $this->siteConfig['meta']['google_verification'] ?? '',
            'siteDescription' => $this->siteConfig['content']['description'] ?? '',
            'siteHistory' => $this->siteConfig['content']['history'] ?? '',
            'team' => $this->siteConfig['team'] ?? [],
            'featuredEnterprises' => []
        ];

        try {
            $viewData['featuredEnterprises'] = $this->entrepriseRepository->getFeaturedEntreprises();
            
            if (empty($viewData['featuredEnterprises'])) {
                $viewData['dbError'] = true;
                $viewData['errorMessage'] = 'Aucune entreprise n\'est disponible pour le moment.';
            }
        } catch (DatabaseException $e) {
            $viewData['dbError'] = true;
            $viewData['errorMessage'] = 'Une erreur est survenue lors de la récupération des données.';
            // Log the error
            error_log("Database error in home page: " . $e->getMessage());
        }

        // Extract variables for the view
        extract($viewData);
        
        // Include the template
        require ROOT_PATH . '/templates/pages/home.php';
    }
}