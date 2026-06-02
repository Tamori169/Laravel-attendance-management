<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceRecordSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role_id', 1)->get();
        $user1 = User::where('role_id', 1)
            ->where('email', 'user1@example.com')
            ->firstOrFail();

        foreach ($users as $user) {
            if ($user->id === $user1->id) {
                $this->createUser1Records($user);
                continue;
            }

            $this->createGeneralUserRecords($user);
        }
    }

    private function createUser1Records(User $user): void
    {
        for ($i = 5; $i >= 1; $i--) {
            $month = Carbon::today()->subMonths($i)->startOfMonth();
            $dates = $this->takeWeekdays($month, 15);

            foreach ($dates as $date) {
                $this->createAttendanceWithBreak(
                    user: $user,
                    date: $date,
                    clockIn: '09:00',
                    clockOut: '18:00',
                    breakIn: '12:00',
                    breakOut: '13:00',
                );
            }
        }

        $currentMonth = Carbon::today()->startOfMonth();
        $dates = $this->takeWeekdays($currentMonth, 17);

        $patterns = [
            ['count' => 10, 'clock_in' => '09:00', 'clock_out' => '18:00'],
            ['count' => 3,  'clock_in' => '09:00', 'clock_out' => '20:00'],
            ['count' => 2,  'clock_in' => '09:30', 'clock_out' => '18:00'],
            ['count' => 1,  'clock_in' => '09:00', 'clock_out' => '17:00'],
            ['count' => 1,  'clock_in' => '08:00', 'clock_out' => '21:00'],
        ];

        $dateIndex = 0;

        foreach ($patterns as $pattern) {
            for ($i = 0; $i < $pattern['count']; $i++) {
                $this->createAttendanceWithBreak(
                    user: $user,
                    date: $dates[$dateIndex],
                    clockIn: $pattern['clock_in'],
                    clockOut: $pattern['clock_out'],
                    breakIn: '12:00',
                    breakOut: '13:00',
                );

                $dateIndex++;
            }
        }
    }

    private function createGeneralUserRecords(User $user): void
    {
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::today()->subMonths($i)->startOfMonth();
            $dates = $this->takeWeekdays($month, 15);

            foreach ($dates as $date) {
                $this->createAttendanceWithBreak(
                    user: $user,
                    date: $date,
                    clockIn: '09:00',
                    clockOut: '18:00',
                    breakIn: '12:00',
                    breakOut: '13:00',
                );
            }
        }
    }

    private function takeWeekdays(Carbon $month, int $count): array
    {
        $dates = [];
        $date = $month->copy()->startOfMonth();

        while (count($dates) < $count && $date->isSameMonth($month)) {
            if ($date->isWeekday()) {
                $dates[] = $date->copy();
            }

            $date->addDay();
        }

        return $dates;
    }

    private function createAttendanceWithBreak(
        User $user,
        Carbon $date,
        string $clockIn,
        string $clockOut,
        string $breakIn,
        string $breakOut,
    ): void {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $this->dateTime($date, $clockIn),
            'clock_out' => $this->dateTime($date, $clockOut),
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => $this->dateTime($date, $breakIn),
            'break_out' => $this->dateTime($date, $breakOut),
        ]);
    }

    private function dateTime(Carbon $date, string $time): string
    {
        [$hour, $minute] = explode(':', $time);

        return $date
            ->copy()
            ->setTime((int) $hour, (int) $minute)
            ->format('Y-m-d H:i:s');
    }
}
