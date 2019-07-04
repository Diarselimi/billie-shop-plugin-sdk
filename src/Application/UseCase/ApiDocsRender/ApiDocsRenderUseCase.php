<?php

namespace App\Application\UseCase\ApiDocsRender;

use App\Infrastructure\OpenApi\RelativeFileReader;

class ApiDocsRenderUseCase
{
    private $resourcesFileReader;

    private const BILLIE_ORANGE_COLOR = '#ff4338';

    private const BILLIE_BLUE_COLOR = '#1f2530';

    private const MONEY_COLOR = '#0aba6e';

    private const SUCCESS_COLOR = '#00e0a8';

    private const SUCCESS_CONTRAST_COLOR = '#017155';

    private const ERROR_COLOR = '#b90016';

    private const ERROR_CONTRAST_COLOR = '#b90016';

    private const EXTRA_DARK_GRAY_COLOR = '#b3aaa8';

    private const DARK_GRAY_COLOR = '#cdc8c7';

    private const GRAY_COLOR = '#e5e2e2';

    private const LIGHT_GRAY_COLOR = '#eaeaea';

    private const EXTRA_LIGHT_GRAY_COLOR = '#f3f3f3';

    private const TEXT_COLOR = self::BILLIE_BLUE_COLOR;

    private const TEXT_SECONDARY_COLOR = '#505f7c';

    private const LOGO_PADDING = '40px';

    private const LOGO_MAX_HEIGHT = '160px';

    private const MAIN_FONT = 'Roboto, Helvetica, sans-serif';

    private const HEADING_FONT = 'Monserrat, Helvetica, sans-serif';

    private const HEADING_FONT_WEIGHT = 'bold';

    private const VERTICAL_SPACING = 20;

    /**
     * @see https://github.com/Redocly/redoc#redoc-options-object
     * @see https://github.com/Redocly/redoc/blob/master/src/theme.ts
     * @var array
     */
    private $templateVars = [
        'title' => 'Billie - PaD API Documentation',
        'spec' => 'billie-pad-openapi.yaml',
        'redoc_config' => [
            'expandResponses' => '200,201,202',
            'pathInMiddlePanel' => true,
            'noAutoAuth' => true,
            'path' => true,
            'requiredPropsFirst' => true,
            'theme' => [
                'colors' => [
                    'primary' => [
                        'main' => self::BILLIE_ORANGE_COLOR,
                    ],
                    'text' => [
                        'primary' => self::TEXT_COLOR,
                        'secondary' => self::TEXT_SECONDARY_COLOR,
                    ],
                    'success' => [
                        'main' => self::SUCCESS_COLOR,
                        'light' => self::SUCCESS_COLOR,
                        'dark' => self::SUCCESS_CONTRAST_COLOR,
                        'contrastText' => self::SUCCESS_CONTRAST_COLOR,
                    ],
                    'error' => [
                        'main' => self::ERROR_COLOR,
                        'light' => self::ERROR_COLOR,
                        'contrastText' => self::ERROR_CONTRAST_COLOR,
                        'dark' => self::ERROR_CONTRAST_COLOR,
                    ],
                    'responses' => [
                        'success' => [
                            'color' => self::SUCCESS_CONTRAST_COLOR,
                        ],
                        'error' => [
                            'color' => self::ERROR_CONTRAST_COLOR,
                        ],
                    ],
                ],
                'typography' => [
                    'fontFamily' => self::MAIN_FONT,
                    'headings' => [
                        // 'fontFamily' => self::HEADING_FONT,
                        'fontWeight' => self::HEADING_FONT_WEIGHT,
                    ],
                ],
                'spacing' => ['sectionVertical' => self::VERTICAL_SPACING],
                'menu' => ['backgroundColor' => self::LIGHT_GRAY_COLOR],
                'rightPanel' => ['backgroundColor' => self::BILLIE_BLUE_COLOR],
                'logo' => [
                    'gutter' => self::LOGO_PADDING,
                    'maxHeight' => self::LOGO_MAX_HEIGHT,
                ],
            ],
        ],
    ];

    public function __construct(RelativeFileReader $resourcesFileReader)
    {
        $this->resourcesFileReader = $resourcesFileReader;
    }

    /**
     * @param  string $spec YAML spec or spec URL
     * @return string
     */
    public function execute(string $spec = 'billie-pad-openapi.yaml'): string
    {
        $templateFile = $this->resourcesFileReader->getFullPath('views/redoc_template.php');
        $this->templateVars['spec'] = $spec;

        return $this->renderTemplate($templateFile);
    }

    private function renderTemplate(string $templateFile): string
    {
        ob_start();
        extract($this->templateVars);
        $redoc_config_json = json_encode($this->templateVars['redoc_config'], JSON_PRETTY_PRINT);
        include $templateFile;
        $htmlCode = ob_get_contents();
        ob_end_clean();

        return $htmlCode;
    }
}
