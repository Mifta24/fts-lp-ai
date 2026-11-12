<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
use App\Services\Reservation\BookingChangeService;
use App\Services\Reservation\ReservationService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Lets a guest look up their own reservation and move or cancel it.
 */
class GuestBookingController extends Controller
{
    private const SUPPORTED_LOCALES = ['id', 'en', 'ja'];

    /**
     * @var array<string, array<string, string>>
     */
    public const LABELS = [
        'en' => [
            'title' => 'Manage my project request', 'intro' => 'Enter the reference from your confirmation and the phone number or email you used.',
            'reference' => 'Request reference', 'contact' => 'Phone or email used for the request', 'find' => 'Find my request',
            'not_found' => 'We could not find a request with those details. Please check the reference and contact.',
            'room' => 'Plan', 'stay' => 'Period', 'nights' => 'days', 'total' => 'Total', 'status' => 'Status',
            'pending' => 'Waiting for FTS to confirm', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
            'new_dates' => 'Move to other dates', 'check_in' => 'Start', 'check_out' => 'End', 'reschedule' => 'Move my dates',
            'cancel' => 'Cancel request', 'confirm_cancel' => 'Cancel this request? The slot is released straight away.',
            'rescheduled' => 'Your dates were moved. FTS will confirm the new dates.', 'cancelled_ok' => 'Your request was cancelled.',
            'unavailable' => 'Those dates are not available for this plan. Please try other dates.',
            'not_changeable' => 'This request can no longer be changed online. Please contact FTS.',
            'invalid' => 'Please check this field.', 'check_in_past' => 'The start date cannot be in the past.', 'check_out_after' => 'The end date must be after the start date.',
            'too_long' => 'Requests are limited to :max days.', 'back' => 'Back to FTS', 'error' => 'Something went wrong. Please try again.',
        ],
        'id' => [
            'title' => 'Kelola permintaan proyek saya', 'intro' => 'Masukkan kode referensi dari konfirmasi dan nomor telepon atau email yang Anda gunakan.',
            'reference' => 'Kode referensi', 'contact' => 'Telepon atau email saat mengajukan', 'find' => 'Cari permintaan saya',
            'not_found' => 'Permintaan dengan data tersebut tidak ditemukan. Mohon periksa kode referensi dan kontak.',
            'room' => 'Paket', 'stay' => 'Periode', 'nights' => 'hari', 'total' => 'Total', 'status' => 'Status',
            'pending' => 'Menunggu konfirmasi FTS', 'confirmed' => 'Terkonfirmasi', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan',
            'new_dates' => 'Pindah ke tanggal lain', 'check_in' => 'Mulai', 'check_out' => 'Selesai', 'reschedule' => 'Pindahkan jadwal',
            'cancel' => 'Batalkan permintaan', 'confirm_cancel' => 'Batalkan permintaan ini? Slot langsung dilepas.',
            'rescheduled' => 'Jadwal dipindahkan. FTS akan mengonfirmasi tanggal baru.', 'cancelled_ok' => 'Permintaan Anda dibatalkan.',
            'unavailable' => 'Tanggal tersebut tidak tersedia untuk paket ini. Silakan coba tanggal lain.',
            'not_changeable' => 'Permintaan ini tidak bisa diubah lagi secara online. Silakan hubungi FTS.',
            'invalid' => 'Mohon periksa kolom ini.', 'check_in_past' => 'Tanggal mulai tidak boleh di masa lalu.', 'check_out_after' => 'Tanggal selesai harus setelah tanggal mulai.',
            'too_long' => 'Periode dibatasi :max hari.', 'back' => 'Kembali ke FTS', 'error' => 'Terjadi kesalahan. Silakan coba lagi.',
        ],
        'ja' => [
            'title' => 'ご相談内容の確認・変更', 'intro' => '確認メッセージの受付番号と、ご相談時の電話番号またはメールアドレスを入力してください。',
            'reference' => '受付番号', 'contact' => 'ご相談時の電話番号またはメール', 'find' => '相談を検索',
            'not_found' => '該当するご相談が見つかりません。受付番号と連絡先をご確認ください。',
            'room' => 'プラン', 'stay' => '期間', 'nights' => '日', 'total' => '合計', 'status' => '状況',
            'pending' => 'FTSの確認待ち', 'confirmed' => '確認済み', 'completed' => '完了', 'cancelled' => 'キャンセル済み',
            'new_dates' => '日程を変更する', 'check_in' => '開始', 'check_out' => '終了', 'reschedule' => '日程を変更',
            'cancel' => '相談をキャンセル', 'confirm_cancel' => 'このご相談をキャンセルしますか？枠はすぐに解放されます。',
            'rescheduled' => '日程を変更しました。FTSが新しい日程を確認します。', 'cancelled_ok' => 'ご相談をキャンセルしました。',
            'unavailable' => 'この日程ではこのプランをご利用いただけません。別の日程をお試しください。',
            'not_changeable' => 'このご相談はオンラインでは変更できません。FTSまでご連絡ください。',
            'invalid' => 'この項目をご確認ください。', 'check_in_past' => '開始日は過去にできません。', 'check_out_after' => '終了日は開始日より後にしてください。',
            'too_long' => '期間は最大:max日までです。', 'back' => 'FTSに戻る', 'error' => 'エラーが発生しました。もう一度お試しください。',
        ],
    ];

    public function __construct(private readonly BookingChangeService $changes) {}

    public function page(Request $request, string $hotelSlug): View
    {
        $hotel = $this->publishedHotel($hotelSlug);
        $locale = $this->locale($request, $hotel);

        return view('hotel.booking-manage', ['hotel' => $hotel, 'locale' => $locale, 'labels' => self::LABELS[$locale]]);
    }

    public function lookup(Request $request, string $hotelSlug): JsonResponse
    {
        $hotel = $this->publishedHotel($hotelSlug);
        $locale = $this->locale($request, $hotel);
        $booking = $this->findBooking($request, $hotel, $locale);

        return $booking ? response()->json($this->summary($booking, $hotel)) : $this->notFound($locale);
    }

    public function reschedule(Request $request, string $hotelSlug): JsonResponse
    {
        $hotel = $this->publishedHotel($hotelSlug);
        $locale = $this->locale($request, $hotel);
        $labels = self::LABELS[$locale];

        $data = $request->validate([
            'check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now($hotel->timezone)->toDateString()],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
        ], ['required' => $labels['invalid'], 'date_format' => $labels['invalid'], 'after_or_equal' => $labels['check_in_past'], 'after' => $labels['check_out_after']]);

        $booking = $this->findBooking($request, $hotel, $locale);

        if (! $booking) {
            return $this->notFound($locale);
        }

        $checkIn = CarbonImmutable::parse($data['check_in'])->startOfDay();
        $checkOut = CarbonImmutable::parse($data['check_out'])->startOfDay();

        if ($checkIn->diffInDays($checkOut) > ReservationService::MAX_NIGHTS) {
            return response()->json(['message' => str_replace(':max', (string) ReservationService::MAX_NIGHTS, $labels['too_long']), 'errors' => ['check_out' => [str_replace(':max', (string) ReservationService::MAX_NIGHTS, $labels['too_long'])]]], 422);
        }

        if (! $this->changes->isChangeable($booking, $hotel)) {
            return response()->json(['message' => $labels['not_changeable']], 409);
        }

        $moved = $this->changes->reschedule($booking, $hotel, $checkIn, $checkOut);

        if (! $moved) {
            return response()->json(['message' => $labels['unavailable'], 'errors' => ['check_in' => [$labels['unavailable']]]], 422);
        }

        return response()->json([...$this->summary($moved->load('roomType'), $hotel), 'message' => $labels['rescheduled']]);
    }

    public function cancel(Request $request, string $hotelSlug): JsonResponse
    {
        $hotel = $this->publishedHotel($hotelSlug);
        $locale = $this->locale($request, $hotel);
        $booking = $this->findBooking($request, $hotel, $locale);

        if (! $booking) {
            return $this->notFound($locale);
        }

        if (! $this->changes->cancel($booking, $hotel)) {
            return response()->json(['message' => self::LABELS[$locale]['not_changeable']], 409);
        }

        return response()->json([...$this->summary($booking->fresh('roomType'), $hotel), 'message' => self::LABELS[$locale]['cancelled_ok']]);
    }

    private function findBooking(Request $request, Hotel $hotel, string $locale): ?Booking
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:20'],
            'contact' => ['required', 'string', 'max:120'],
            'locale' => ['nullable', Rule::in(self::SUPPORTED_LOCALES)],
        ], ['required' => self::LABELS[$locale]['invalid']]);

        return $this->changes->findForGuest($hotel, $data['reference'], $data['contact']);
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(Booking $booking, Hotel $hotel): array
    {
        return [
            'reference' => $booking->reference,
            'room' => $booking->roomType->name,
            'rooms' => $booking->room_count,
            'check_in' => $booking->check_in->toDateString(),
            'check_out' => $booking->check_out->toDateString(),
            'nights' => $booking->nights(),
            'adults' => $booking->adults,
            'children' => $booking->children,
            'total' => (float) $booking->total_price,
            'currency' => $hotel->currency,
            'status' => $booking->status,
            'changeable' => $this->changes->isChangeable($booking, $hotel),
        ];
    }

    private function notFound(string $locale): JsonResponse
    {
        return response()->json(['message' => self::LABELS[$locale]['not_found']], 404);
    }

    private function locale(Request $request, Hotel $hotel): string
    {
        $locale = $request->input('locale', $request->query('lang'));

        return in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : $hotel->default_locale;
    }

    private function publishedHotel(string $hotelSlug): Hotel
    {
        $hotel = Hotel::where('slug', $hotelSlug)->first();

        abort_if(! $hotel || ! $hotel->isPublished(), 404);

        return $hotel;
    }
}
