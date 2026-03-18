<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Puzzle;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

final class OGImageController
{
    public function __invoke(string $id)
    {
        $puzzle = Puzzle::with('user')->findOrFail($id);

        $manager = new ImageManager(new Driver());

        $image = $manager->read(\public_path('images/og-blank.jpg'));

        $image->text('Clonections', 600, 75, static function ($font): void {
            $font->filename(\public_path('fonts/serif-bold.ttf'));
            $font->size(64);
            $font->color('#353530');
            $font->align('center');
            $font->valign('middle');
        });

        $image->text(\strtoupper($puzzle->title), 600, 300, static function ($font): void {
            $font->filename(\public_path('fonts/sans-bold.ttf'));
            $font->size(80);
            $font->color('#111827');
            $font->align('center');
            $font->valign('middle');
        });

        if ($puzzle->user) {
            $image->text(\strtoupper(\sprintf('By %s', $puzzle->user->name)), 600, 380, static function ($font): void {
                $font->filename(\public_path('fonts/sans-bold.ttf')); // Can use a different font here if you want
                $font->size(40);
                $font->color('#6b7280'); // Tailwind gray-500
                $font->align('center');
                $font->valign('middle');
            });
        }

        return \response($image->toJpeg(90)->toString(), 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=604800'
        ]);
    }
}
