<?php

namespace App\Services\Admin;

use App\Repositories\Admin\SocialMediaLink\SocialMediaLinkRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class SocialMediaLinkService
{
    protected $socialMediaLinkRepository;

    public function __construct(SocialMediaLinkRepositoryInterface $socialMediaLinkRepository)
    {
        $this->socialMediaLinkRepository = $socialMediaLinkRepository;
    }

    public function getAllSocialMediaLinks()
    {
        return $this->socialMediaLinkRepository->all();
    }

    public function createSocialMediaLink($data)
    {
        $createData = [
            'type' => $data['type'],
            'platform' => $data['platform'],
            'link' => $data['link'] ?? null,
        ];

        if ($data['type'] === 'wechat' && !empty($data['wechat_qr_image'])) {
            $createData['wechat_qr_image'] = $data['wechat_qr_image']->store('social/wechat', 'public');
        }

        $socialMediaLink = $this->socialMediaLinkRepository->create($createData);

        foreach (clean_translations($data['languages'] ?? []) as $languageCode => $translationData) {
            $this->socialMediaLinkRepository->storeTranslation($socialMediaLink->id, $languageCode, $translationData['name'] ?? '');
        }

        return $socialMediaLink;
    }

    public function updateSocialMediaLink($id, $data)
    {
        $updateData = [
            'type' => $data['type'],
            'platform' => $data['platform'],
            'link' => $data['link'] ?? null,
        ];

        $existing = $this->socialMediaLinkRepository->find($id);

        if ($data['type'] === 'wechat' && !empty($data['wechat_qr_image'])) {
            if ($existing->wechat_qr_image) {
                Storage::disk('public')->delete($existing->wechat_qr_image);
            }
            $updateData['wechat_qr_image'] = $data['wechat_qr_image']->store('social/wechat', 'public');
        } elseif ($data['type'] !== 'wechat') {
            $updateData['wechat_qr_image'] = null;
        }

        $socialMediaLink = $this->socialMediaLinkRepository->update($id, $updateData);

        foreach (clean_translations($data['languages'] ?? []) as $languageCode => $translationData) {
            $this->socialMediaLinkRepository->updateTranslation($socialMediaLink->id, $languageCode, $translationData['name'] ?? '');
        }

        return $socialMediaLink;
    }

    public function deleteSocialMediaLink($id)
    {
        $this->socialMediaLinkRepository->delete($id);
    }
}
