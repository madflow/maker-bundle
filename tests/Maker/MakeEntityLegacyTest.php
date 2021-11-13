<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\MakerBundle\Tests\Maker;

use Symfony\Bundle\MakerBundle\Maker\MakeEntity;
use Symfony\Bundle\MakerBundle\Test\MakerTestCase;
use Symfony\Bundle\MakerBundle\Test\MakerTestDetails;
use Symfony\Component\Finder\Finder;

class MakeEntityLegacyTest extends MakerTestCase
{
    public function getTestDetails()
    {
        yield 'entity_new' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // add not additional fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntity')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_new_api_resource' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // Mark the entity as an API Platform resource
                'y',
                // add not additional fields
                '',
            ])
            ->addExtraDependencies('api')
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntity')
            ->configureDatabase()
            ->updateSchemaAfterCommand()
            ->assert(function (string $output, string $directory) {
                $this->assertFileExists($directory.'/src/Entity/User.php');

                $content = file_get_contents($directory.'/src/Entity/User.php');
                $this->assertStringContainsString('use ApiPlatform\Core\Annotation\ApiResource;', $content);
                $this->assertStringContainsString(\PHP_VERSION_ID >= 80000 ? '#[ApiResource]' : '@ApiResource', $content);
            }),
        ];

        yield 'entity_with_fields' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // add not additional fields
                'name',
                'string',
                '255', // length
                // nullable
                'y',
                'createdAt',
                // use default datetime
                '',
                // nullable
                'y',
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntity')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_updating_main' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // add additional fields
                'lastName',
                'string',
                '', // length (default 255)
                // nullable
                'y',
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityUpdate')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_many_to_one_simple_with_inverse' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'UserAvatarPhoto',
                // field name
                'user',
                // add a relationship field
                'relation',
                // the target entity
                'User',
                // relation type
                'ManyToOne',
                // nullable
                'n',
                // do you want to generate an inverse relation? (default to yes)
                '',
                // field name on opposite side - use default 'userAvatarPhotos'
                '',
                // orphanRemoval (default to no)
                '',
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityManyToOne')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_many_to_one_simple_no_inverse' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'UserAvatarPhoto',
                // field name
                'user',
                // add a relationship field
                'relation',
                // the target entity
                'User',
                // relation type
                'ManyToOne',
                // nullable
                'n',
                // do you want to generate an inverse relation? (default to yes)
                'n',
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityManyToOneNoInverse')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_many_to_one_self_referencing' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // field name
                'guardian',
                // add a relationship field
                'relation',
                // the target entity
                'User',
                // relation type
                'ManyToOne',
                // nullable
                'y',
                // do you want to generate an inverse relation? (default to yes)
                '',
                // field name on opposite side
                'dependants',
                // orphanRemoval (default to no)
                '',
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntitySelfReferencing')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_exists_in_root' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'Directory',
                // field name
                'parentDirectory',
                // add a relationship field
                'relation',
                // the target entity
                'Directory',
                // relation type
                'ManyToOne',
                // nullable
                'y',
                // do you want to generate an inverse relation? (default to yes)
                '',
                // field name on opposite side
                'childDirectories',
                // orphanRemoval (default to no)
                '',
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityExistsInRoot')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_one_to_many_simple' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // field name
                'photos',
                // add a relationship field
                'relation',
                // the target entity
                'UserAvatarPhoto',
                // relation type
                'OneToMany',
                // field name on opposite side - use default 'user'
                '',
                // nullable
                'n',
                // orphanRemoval
                'y',
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityOneToMany')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_many_to_many_simple' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'Course',
                // field name
                'students',
                // add a relationship field
                'relation',
                // the target entity
                'User',
                // relation type
                'ManyToMany',
                // inverse side?
                'y',
                // field name on opposite side - use default 'courses'
                '',
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityManyToMany')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_many_to_many_simple_in_custom_root_namespace' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'Course',
                // field name
                'students',
                // add a relationship field
                'relation',
                // the target entity
                'User',
                // relation type
                'ManyToMany',
                // inverse side?
                'y',
                // field name on opposite side - use default 'courses'
                '',
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityManyToManyInCustomNamespace')
            ->changeRootNamespace('Custom')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_one_to_one_simple' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'UserProfile',
                // field name
                'user',
                // add a relationship field
                'relation',
                // the target entity
                'User',
                // relation type
                'OneToOne',
                // nullable
                'n',
                // inverse side?
                'y',
                // field name on opposite side - use default 'userProfile'
                '',
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityOneToOne')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        yield 'entity_many_to_one_vendor_target' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // field name
                'userGroup',
                // add a relationship field
                'ManyToOne',
                // the target entity
                'Some\\Vendor\\Group',
                // nullable
                '',
                /*
                 * normally, we ask for the field on the *other* side, but we
                 * do not here, since the other side won't be mapped.
                 */
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityRelationVendorTarget')
            ->configureDatabase()
            ->addReplacement(
                'composer.json',
                '"App\\\Tests\\\": "tests/",',
                '"App\\\Tests\\\": "tests/",'."\n".'            "Some\\\Vendor\\\": "vendor/some-vendor/src",'
            )
            ->assert(function (string $output, string $directory) {
                $this->assertStringContainsString('updated: src/Entity/User.php', $output);
                $this->assertStringNotContainsString('updated: vendor/', $output);

                // sanity checks on the generated code
                $finder = new Finder();
                $finder->in($directory.'/src/Entity')->files()->name('*.php');
                $this->assertCount(1, $finder);

                $this->assertStringNotContainsString('inversedBy', file_get_contents($directory.'/src/Entity/User.php'));
            }),
        ];

        yield 'entity_many_to_many_vendor_target' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // field name
                'userGroups',
                // add a relationship field
                'ManyToMany',
                // the target entity
                'Some\Vendor\Group',
                /*
                 * normally, we ask for the field on the *other* side, but we
                 * do not here, since the other side won't be mapped.
                 */
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityRelationVendorTarget')
            ->configureDatabase()
            ->addReplacement(
                'composer.json',
                '"App\\\Tests\\\": "tests/",',
                '"App\\\Tests\\\": "tests/",'."\n".'            "Some\\\Vendor\\\": "vendor/some-vendor/src",'
            )
            ->assert(function (string $output, string $directory) {
                $this->assertStringNotContainsString('updated: vendor/', $output);

                $this->assertStringNotContainsString('inversedBy', file_get_contents($directory.'/src/Entity/User.php'));
            }),
        ];

        yield 'entity_one_to_one_vendor_target' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // field name
                'userGroup',
                // add a relationship field
                'OneToOne',
                // the target entity
                'Some\Vendor\Group',
                // nullable,
                '',
                /*
                 * normally, we ask for the field on the *other* side, but we
                 * do not here, since the other side won't be mapped.
                 */
                // finish adding fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityRelationVendorTarget')
            ->configureDatabase()
            ->addReplacement(
                'composer.json',
                '"App\\\Tests\\\": "tests/",',
                '"App\\\Tests\\\": "tests/",'."\n".'            "Some\\\Vendor\\\": "vendor/some-vendor/src",'
            )
            ->assert(function (string $output, string $directory) {
                $this->assertStringNotContainsString('updated: vendor/', $output);

                $this->assertStringNotContainsString('inversedBy', file_get_contents($directory.'/src/Entity/User.php'));
            }),
        ];

        yield 'entity_regenerate' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // namespace: use default App\Entity
                '',
            ])
            ->setArgumentsString('--regenerate')
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityRegenerate')
            ->configureDatabase(true),
        ];

        yield 'entity_regenerate_embeddable_object' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // namespace: use default App\Entity
                '',
            ])
            ->setArgumentsString('--regenerate')
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityRegenerateEmbeddableObject')
            ->configureDatabase(),
        ];

        yield 'entity_regenerate_embeddable' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // namespace: use default App\Entity
                '',
            ])
            ->setArgumentsString('--regenerate --overwrite')
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityRegenerateEmbedable')
            ->configureDatabase(),
        ];

        yield 'entity_regenerate_overwrite' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // namespace: use default App\Entity
                '',
            ])
            ->setArgumentsString('--regenerate --overwrite')
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityRegenerateOverwrite')
            ->configureDatabase(false),
        ];

        yield 'entity_updating_overwrite' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // field name
                'firstName',
                'string',
                '', // length (default 255)
                // nullable
                '',
                // finish adding fields
                '',
            ])
            ->setArgumentsString('--overwrite')
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntityOverwrite'),
        ];

        // see #192
        yield 'entity_into_sub_namespace_matching_entity' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'Product\\Category',
                // add not additional fields
                '',
            ])
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntitySubNamespaceMatchingEntity')
            ->configureDatabase()
            ->updateSchemaAfterCommand(),
        ];

        $broadCastTest = MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // Mark the entity as broadcasted
                'y',
                // add not additional fields
                '',
            ])
            ->setRequiredPhpVersion(70200)
            ->addExtraDependencies('symfony/ux-turbo-mercure')
            ->configureDatabase()
            ->addReplacement(
                '.env',
                'https://example.com/.well-known/mercure',
                'http://127.0.0.1:1337/.well-known/mercure'
            )
            ->updateSchemaAfterCommand()
            ->assert(function (string $output, string $directory) {
                $this->assertFileExists($directory.'/src/Entity/User.php');

                $content = file_get_contents($directory.'/src/Entity/User.php');
                $this->assertStringContainsString('use Symfony\UX\Turbo\Attribute\Broadcast;', $content);
                $this->assertStringContainsString(\PHP_VERSION_ID >= 80000 ? '#[Broadcast]' : '@Broadcast', $content);
            })
        ;
        // use the fixtures - which contains a test for Mercure - unless specified to skip those
        $skipMercureTest = $_SERVER['MAKER_SKIP_MERCURE_TEST'] ?? false;
        if (!$skipMercureTest) {
            $broadCastTest->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntity');
        }
        yield 'entity_new_broadcast' => [$broadCastTest];

        yield 'entity_new_with_api_and_broadcast_dependencies' => [MakerTestDetails::createTest(
            $this->getMakerInstance(MakeEntity::class),
            [
                // entity class name
                'User',
                // Mark the entity as not an API Platform resource
                'n',
                // Mark the entity as not broadcasted
                'n',
                // add not additional fields
                '',
            ])
            ->setRequiredPhpVersion(70200)
            ->addExtraDependencies('api')
            ->addExtraDependencies('symfony/ux-turbo-mercure')
            ->setFixtureFilesPath(__DIR__.'/../fixtures/legacy/MakeEntity/MakeEntity')
            ->configureDatabase()
            ->updateSchemaAfterCommand()
            ->assert(function (string $output, string $directory) {
                $this->assertFileExists($directory.'/src/Entity/User.php');
            }),
        ];
    }
}