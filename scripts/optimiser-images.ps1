
# ===============================================
# CHEZ AKA - OPTIMISATION DES PHOTOGRAPHIES
# Conversion JPG vers WebP
# Qualite : 82
# Les JPG originaux sont conserves.
# ===============================================

$ErrorActionPreference = "Stop"

# Chemin vers les images du restaurant
$imagesDir = Join-Path $PSScriptRoot "..\public\images\chez-aka"

# Liste des images et dimensions maximales
$images = @(
    @{
        Name = "hero-desktop"
        Size = "1920x1280>"
    },
    @{
        Name = "hero-mobile"
        Size = "1000x1400>"
    },
    @{
        Name = "cuisine"
        Size = "1200x1200>"
    },
    @{
        Name = "musique"
        Size = "1200x1200>"
    },
    @{
        Name = "facade"
        Size = "1200x1200>"
    },
    @{
        Name = "click-collect"
        Size = "1200x1200>"
    }
)

# Verifier la presence d'ImageMagick
if (-not (Get-Command magick -ErrorAction SilentlyContinue)) {
    throw "ImageMagick est introuvable."
}

# Parcourir toutes les images
foreach ($image in $images) {

    $source = Join-Path $imagesDir ($image.Name + ".jpg")

    $destination = Join-Path $imagesDir ($image.Name + ".webp")

    if (-not (Test-Path -LiteralPath $source)) {
        throw "Image introuvable : $source"
    }

    # Conversion en WebP
    & magick "${source}[0]" `
        -auto-orient `
        -resize $image.Size `
        -strip `
        -quality 82 `
        -define "webp:method=6" `
        $destination

    if ($LASTEXITCODE -ne 0) {
        throw "Erreur de conversion : $source"
    }

    # Calcul de la reduction de poids
    $original = (Get-Item -LiteralPath $source).Length

    $optimise = (Get-Item -LiteralPath $destination).Length

    if ($optimise -eq 0) {
        throw "Le fichier genere est vide : $destination"
    }

    $reduction = [math]::Round(
        (1 - ($optimise / $original)) * 100,
        1
    )

    Write-Host "$($image.Name) : reduction de $reduction %"
}

Write-Host "Optimisation terminee avec succes."
