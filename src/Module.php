<?php
declare(strict_types=1);

namespace LotGD\Module\Res\Form;

use LotGD\Core\Game;
use LotGD\Core\Events\EventContext;
use LotGD\Core\Models\SceneAttachment;
use LotGD\Core\Module as ModuleInterface;
use LotGD\Core\Models\Module as ModuleModel;
use LotGD\Module\Res\Form\SceneAttachments\FormAttachment;

class Module implements ModuleInterface {
    /**
     * Handles the events you've registered in lotgd.yml
     * @param Game $g
     * @param EventContext $context
     * @return EventContext
     */
    public static function handleEvent(Game $g, EventContext $context): EventContext
    {
        return match ($context->getEvent()) {
            "h/lotgd/crate/html/displayScene/renderAttachment/" . FormAttachment::class => self::renderForCrateHtml($g, $context),
            default => $context
        };
    }

    /**
     * Converts a form from a formattachment to a view.
     * @param Game $g
     * @param EventContext $context
     * @return EventContext
     */
    public static function renderForCrateHtml(Game $g, EventContext $context): EventContext
    {
        /** @var FormAttachment $attachment */
        $attachment = $context->getDataField("attachment");

        if ($attachment instanceof FormAttachment) {
            if ($attachment->hasForm()) {
                $context->setDataField("renderedAttachment", [
                    "form" => $attachment->getForm()->createView(),
                ]);
            } else {
                $g->getLogger()->debug("FormAttachment has no form");
            }
        }

        return $context;
    }

    public static function onRegister(Game $g, ModuleModel $module)
    {
        $em = $g->getEntityManager();
        $logger = $g->getLogger();

        $formSceneAttachmentEntry = new SceneAttachment(
            class: FormAttachment::class,
            title: "Form Attachment",
            userAssignable: false
        );

        $logger->debug("module-res-form: onRegister: SceneAttachment for FormAttachment has been persisted.");

        $em->persist($formSceneAttachmentEntry);
    }

    public static function onUnregister(Game $g, ModuleModel $module)
    {
        $em = $g->getEntityManager();
        $logger = $g->getLogger();

        $entry = $em->getRepository(SceneAttachment::class)->find(FormAttachment::class);

        if ($entry) {
            $logger->debug("module-res-form: onUnregister: SceneAttachment for FormAttachment has been removed.");
            $em->remove($entry);
        } else {
            $logger->debug("odule-res-form: onUnregister: SceneAttachment for FormAttachment has not been found.");
        }
    }
}
