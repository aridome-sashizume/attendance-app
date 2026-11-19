<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttendanceApiController extends Controller
{
    /**
     * 勤怠一覧取得API
     *
     * クエリパラメータに応じて勤怠情報を検索・絞り込みし、
     * ページネーションした結果を返却する。
     *
     * 対応パラメータ
     * - user_id : ユーザーIDによる絞り込み
     * - date    : 日付による絞り込み
     * - month   : 年月による絞り込み
     * - page    : ページ番号
     * - per_page: 1ページあたりの取得件数
     */
    public function index(
        IndexAttendanceRecordRequest $request
    ): AnonymousResourceCollection {

        // 1ページあたりの取得件数（指定がない場合は20件）
        $perPage = $request->integer(
            'per_page', 20
        );

        // 勤怠一覧取得
        $attendanceRecords = AttendanceRecord::query()

            // 関連データをEager LoadしてN+1問題を防止
            ->with([
                'user',
                'attendanceBreaks',
            ])

            // ユーザーIDによる絞り込み
            ->when(
                $request->user_id,
                fn ($query, int $userId) => $query->where(
                    'user_id',
                    $userId
                )
            )

            // 日付による絞り込み
            ->when(
                $request->date,
                fn ($query, string $date) => $query->whereDate(
                    'date',
                    $date
                )
            )

            // 年月による絞り込み
            ->when(
                $request->month,
                function (
                    $query,
                    string $month
                ) {
                    return $query
                        ->whereYear(
                            'date',
                            substr($month, 0, 4)
                        )
                        ->whereMonth(
                            'date',
                            substr($month, 5, 2)
                        );
                }
            )

            // 日付降順で並び替え
            ->latest('date')

            // ページネーション
            ->paginate($perPage);

        // Resource形式でレスポンスを返却
        return AttendanceRecordResource::collection(
            $attendanceRecords
        );
    }

    /**
     * 勤怠詳細取得API
     */
    public function show(AttendanceRecord $attendanceRecord): AttendanceRecordResource
    {
        // 関連データをEager LoadしてN+1問題を防止
        $attendanceRecord->load([
            'user',
            'attendanceBreaks',
            'applications',
        ]);

        // Resource形式で返却
        return new AttendanceRecordResource(
            $attendanceRecord
        );
    }

    /**
     * 勤怠登録API
     *
     * 認証済みユーザーの勤怠情報を登録する。
     *
     * バリデーション済みのデータを利用して
     * 勤怠情報を作成する。
     */
    /**
     * 勤怠登録API
     *
     * リクエストで受け取った勤怠情報を登録する。
     *
     * FormRequestでバリデーション済みのデータを利用して
     * 勤怠レコードを作成し、登録結果を返却する。
     */
    public function store(
        StoreAttendanceRecordRequest $request
    ): AttendanceRecordResource {

        // 勤怠情報を登録
        $attendanceRecord = AttendanceRecord::create(
            $request->validated()
        );

        // Resource形式で返却
        return new AttendanceRecordResource(
            $attendanceRecord
        );
    }
}
