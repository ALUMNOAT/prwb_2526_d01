<?php

class Image {

    /**
     * Redimensionne une image source et la convertit en JPEG.
     *
     * - Conserve les proportions originales
     * - Ne dépasse jamais les dimensions max ($maxW / $maxH)
     * - Compresse l'image finale en JPEG
     *
     * @param string $src Chemin du fichier source (image uploadée)
     * @param string $dest Chemin du fichier JPEG de destination
     * @param int $maxW Largeur maximale autorisée
     * @param int $maxH Hauteur maximale autorisée
     * @param int $quality Qualité de compression JPEG (0–100)
     * @throws Exception
     */
    public static function resizeToJpeg(
        string $src,
        string $dest,
        int $maxW,
        int $maxH,
        int $quality
    ): void {

        // Récupère les dimensions originales de l’image et son type
        // $w = largeur, $h = hauteur, $type = type IMAGETYPE_*
        [$w, $h, $type] = getimagesize($src);

        // Calcul du ratio de redimensionnement
        // min(...) garantit :
        // - pas de dépassement des dimensions max
        // - pas d’agrandissement (ratio <= 1)
        $ratio = min($maxW / $w, $maxH / $h, 1);

        // Nouvelles dimensions en conservant les proportions
        $newW = (int)($w * $ratio);
        $newH = (int)($h * $ratio);

        // Création d’une image vide (canvas) aux nouvelles dimensions
        $dstImg = imagecreatetruecolor($newW, $newH);

        // Création de l’image source selon son format d’origine
        // (support JPEG, PNG, GIF et WEBP)
        $srcImg = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_GIF  => imagecreatefromgif($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            default => throw new Exception("Type d'image non supporté")
        };

        // Copie et redimensionnement de l’image source vers l’image destination
        imagecopyresampled(
            $dstImg,
            $srcImg,
            0, 0,           // Position de départ (destination)
            0, 0,           // Position de départ (source)
            $newW, $newH,   // Dimensions destination
            $w, $h          // Dimensions source
        );

        // Sauvegarde finale de l’image en JPEG avec compression
        imagejpeg($dstImg, $dest, $quality);

        // Libération de la mémoire (important avec GD)
        imagedestroy($srcImg);
        imagedestroy($dstImg);
    }
}
