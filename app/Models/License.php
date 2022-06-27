<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

/**
 * App\Models\License
 *
 * @property int $id
 * @property string $reference
 * @property bool $isDeprecatedLicenseId
 * @property string $detailsUrl
 * @property int $referenceNumber
 * @property string $name
 * @property string $licenseId
 * @property string $seeAlso
 * @property bool $isOsiApproved
 * @property bool $isFsfLibre
 * @method static \Illuminate\Database\Eloquent\Builder|License newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|License newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|License query()
 * @method static \Illuminate\Database\Eloquent\Builder|License whereDetailsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereIsDeprecatedLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereIsFsfLibre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereIsOsiApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereSeeAlso($value)
 * @mixin \Eloquent
 */
class License extends Model
{
    use Sushi;

    /** @var string[] $schema */
    protected $schema = [
        'id' => 'integer',
        'reference' => 'string',
        'isDeprecatedLicenseId' => 'boolean',
        'detailsUrl'  => 'string',
        'referenceNumber' => 'integer',
        'name' => 'string',
        'licenseId' => 'string',
        'seeAlso' => 'string',
        'isOsiApproved' => 'boolean',
        'isFsfLibre' => 'boolean',
    ];

    /**
     * @return string[]
     * @throws \JsonException
     */
    public function getRows(): array
    {
        /** @var string $json */
        $json = file_get_contents(resource_path('assets/licenses.json'));
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $licenses = [];
        foreach ($data['licenses'] as $license) {
            if (!isset($license['isFsfLibre'])) {
                $license['isFsfLibre'] = false;
            }
            if (!isset($license['isOsiApproved'])) {
                $license['isOsiApproved'] = false;
            }
            $license['seeAlso'] = implode(', ', $license['seeAlso']);
            $licenses[] = $license;
        }
        return $licenses;
    }

    protected function sushiShouldCache(): bool
    {
        return false;
    }

    protected function sushiCacheReferencePath(): string
    {
        return resource_path('assets/licenses.json');
    }
}
