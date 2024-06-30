<?php

namespace App\Kernel\Ui\Form\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Vich\UploaderBundle\Form\Type\VichImageType;

class VichImageField implements FieldInterface
{
    use FieldTrait;
    public const OPTION_DOWNLOAD_URI = 'download_uri';
    public const OPTION_IMAGE_URI = 'image_uri';
    public const OPTION_UPLOADED_FILE_NAME_PATTERN = 'uploadedFileNamePattern';

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(VichImageType::class)
            ->setCustomOption(self::OPTION_IMAGE_URI, null)
            ->setCustomOption(self::OPTION_DOWNLOAD_URI, null)
        ;
    }

    public function setImageUri(?string $imageUri): self
    {
        $this->setCustomOption(self::OPTION_IMAGE_URI, $imageUri);

        return $this;
    }

    public function setDownloadUri(?string $downloadUri): self
    {
        $this->setCustomOption(self::OPTION_DOWNLOAD_URI, $downloadUri);

        return $this;
    }

    public function setUploadedFileNamePattern(?string $patternOrCallable): self
    {
        $this->setCustomOption(self::OPTION_UPLOADED_FILE_NAME_PATTERN, $patternOrCallable);

        return $this;
    }

}
