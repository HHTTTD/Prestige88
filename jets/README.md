# Prestige88 - Private Jet Booking System

## ฟีเจอร์ใหม่: การแก้ไข Ticket Details สำหรับ Admin

### ฟีเจอร์ที่เพิ่มเข้ามา:

#### 1. หน้าแก้ไข Booking สำหรับ Admin
- **URL**: `?page=edit_booking&booking_id={booking_id}`
- **การเข้าถึง**: เฉพาะ admin เท่านั้น
- **ฟีเจอร์**:
  - แก้ไขข้อมูลการจองทั้งหมด
  - อัปเดตสถานะการจอง
  - แก้ไขรายละเอียดการเดินทาง
  - แก้ไขความต้องการพิเศษ
  - คำนวณราคาใหม่อัตโนมัติ

#### 2. ฟิลด์ที่สามารถแก้ไขได้:
- **Departure Location**: สถานที่ออก발
- **Arrival Location**: สถานที่ปลายทาง
- **Departure Date**: วันที่ออกเดินทาง
- **Departure Time**: เวลาออกเดินทาง
- **Number of Passengers**: จำนวนผู้โดยสาร
- **Flight Hours**: ชั่วโมงการบิน
- **Status**: สถานะการจอง (Pending, Confirmed, Cancelled, Completed)
- **Special Requests**: ความต้องการพิเศษ

#### 3. การเข้าถึงฟีเจอร์:
- **ในหน้า Dashboard**: คลิกปุ่ม "Edit" ในตาราง Latest Bookings
- **ในหน้า Bookings**: คลิกปุ่มแก้ไข (ไอคอนดินสอ) สำหรับ admin

#### 4. ระบบความปลอดภัย:
- ตรวจสอบสิทธิ์ admin ก่อนเข้าถึง
- ตรวจสอบการมีอยู่ของ booking
- บันทึกประวัติการแก้ไข (updated_at, updated_by)

#### 5. การคำนวณราคา:
- คำนวณราคาใหม่ตามชั่วโมงการบิน
- คำนวณส่วนลดสมาชิก
- อัปเดตราคารวมอัตโนมัติ

### วิธีการใช้งาน:

1. **เข้าสู่ระบบเป็น Admin**
   - Username: `admin`
   - Password: `admin123`

2. **ไปที่หน้า Dashboard**
   - URL: `?page=database`

3. **คลิกปุ่ม "Edit" ในตาราง Latest Bookings**

4. **แก้ไขข้อมูลที่ต้องการ**

5. **คลิก "Update Booking" เพื่อบันทึก**

### ตัวอย่างข้อมูลที่แก้ไขได้:

```json
{
  "departure_location": "Bangkok",
  "arrival_location": "Phuket",
  "departure_date": "2025-06-25",
  "departure_time": "14:30",
  "passengers": 6,
  "flight_hours": 3,
  "status": "confirmed",
  "special_requests": "ต้องการอาหารพิเศษสำหรับผู้โดยสาร"
}
```

### การตอบสนองของระบบ:

- **สำเร็จ**: แสดงข้อความ "Booking updated successfully!" และกลับไปหน้า Dashboard
- **ไม่สำเร็จ**: แสดงข้อความ error และให้ลองใหม่

### หมายเหตุ:

- ฟีเจอร์นี้ใช้เฉพาะ admin เท่านั้น
- การแก้ไขจะบันทึกประวัติการเปลี่ยนแปลง
- ราคาจะถูกคำนวณใหม่อัตโนมัติ
- สามารถแก้ไขได้แม้ booking จะยืนยันแล้ว 