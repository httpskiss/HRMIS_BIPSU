<?php
require_once 'auth/db.php';

class Attendance {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Mark attendance
    public function markAttendance($userId, $date, $status, $checkIn = null, $checkOut = null, $notes = null) {
        $stmt = $this->pdo->prepare("INSERT INTO attendance (user_id, date, status, check_in, check_out, notes) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $date, $status, $checkIn, $checkOut, $notes]);
    }

    // Get daily attendance
    public function getDailyAttendance($date) {
        $stmt = $this->pdo->prepare("SELECT a.*, u.first_name, u.last_name, u.department 
                                    FROM attendance a
                                    JOIN users u ON a.user_id = u.id
                                    WHERE a.date = ?");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }

    // Update attendance record
    public function updateAttendance($id, $status, $checkIn, $checkOut, $notes = null) {
        $stmt = $this->pdo->prepare("UPDATE attendance SET 
                                    status = ?, check_in = ?, check_out = ?, notes = ?
                                    WHERE id = ?");
        return $stmt->execute([$status, $checkIn, $checkOut, $notes, $id]);
    }

    // Delete attendance record
    public function deleteAttendance($id) {
        $stmt = $this->pdo->prepare("DELETE FROM attendance WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Get monthly summary
    public function getMonthlySummary($month, $year) {
        $stmt = $this->pdo->prepare("SELECT 
                                    date,
                                    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                                    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                                    SUM(CASE WHEN status = 'On Leave' THEN 1 ELSE 0 END) as on_leave,
                                    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
                                    SUM(CASE WHEN status = 'Half Day' THEN 1 ELSE 0 END) as half_day
                                    FROM attendance
                                    WHERE MONTH(date) = ? AND YEAR(date) = ?
                                    GROUP BY date");
        $stmt->execute([$month, $year]);
        return $stmt->fetchAll();
    }
}