<?php

declare(strict_types=1);

namespace ETSGlobal\LogBundle\Monolog\Handler;

use ETSGlobal\LogBundle\Monolog\Handler\ContentDataModifier\ContentDataModifierInterface;
use ETSGlobal\LogBundle\Monolog\Handler\ExclusionStrategy\ExclusionStrategyInterface;
use Monolog\Handler\SlackHandler as BaseSlackHandler;
use Monolog\Level;
use Monolog\LogRecord;

/** @internal */
final class SlackHandler extends BaseSlackHandler
{
    private array $exclusionStrategies = [];

    private array $contentDataModifiers = [];

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $token,
        string $channel,
        ?string $username = null,
        bool $useAttachment = true,
        ?string $iconEmoji = null,
        int|string|Level $level = Level::Critical,
        bool $bubble = true,
        bool $useShortAttachment = false,
        bool $includeContextAndExtra = false,
        array $excludeFields = [],
    ) {
        parent::__construct(
            $token,
            $channel,
            sprintf('[%s] %s', gethostname(), $username),
            $useAttachment,
            $iconEmoji,
            $level,
            $bubble,
            $useShortAttachment,
            $includeContextAndExtra,
            $excludeFields,
        );
    }

    public function addExclusionStrategy(ExclusionStrategyInterface $exclusionStrategies): void
    {
        $this->exclusionStrategies[] = $exclusionStrategies;
    }

    public function addContentDataModifier(ContentDataModifierInterface $contentDataModifier): void
    {
        $this->contentDataModifiers[] = $contentDataModifier;
    }

    public function isHandling(LogRecord $record): bool
    {
        foreach ($this->exclusionStrategies as $exclusionStrategy) {
            if (true === $exclusionStrategy->excludeRecord($record)) {
                return false;
            }
        }

        return parent::isHandling($record);
    }

    /** phpcs:disable */
    protected function prepareContentData(LogRecord $record): array
    {
        $dataArray = parent::prepareContentData($record);

        if (isset($dataArray['attachments'])) {
            $dataArray['attachments'] = json_decode($dataArray['attachments'], true);
        }

        foreach ($this->contentDataModifiers as $contentDataModifier) {
            $contentDataModifier->modify($dataArray, $record);
        }

        $dataArray['attachments'] = json_encode($dataArray['attachments']);

        /** @var array<string> $dataArray */
        return $dataArray;
    }
}
