<?php
// PDF Generator for Prestige Jets
class PDFGenerator {
    private $pdf;
    
    public function __construct() {
        // We'll use a simple HTML to PDF solution
        $this->pdf = null;
    }
    
    public function generateBookingsPDF($userData, $bookings) {
        ob_start();
        
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Prestige Jets - Booking Statement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .company-address {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }
        .customer-info {
            margin: 30px 0;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .info-value {
            color: #34495e;
        }
        .bookings-section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        .booking-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fafafa;
        }
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .booking-id {
            font-weight: bold;
            color: #2980b9;
        }
        .booking-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background-color: #2c3e50;
            color: white;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #3498db;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">Prestige Jets</div>
            <div class="company-address">
                Premium Private Jet Charter Service<br>
                Bangkok, Thailand<br>
                Tel: +66 26 647 7488 | Email: contact@prestige88.com
            </div>
        </div>
        
        <div class="customer-info">
            <div class="info-row">
                <span class="info-label">Customer ID:</span>
                <span class="info-value">' . htmlspecialchars($userData['id']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Customer Name:</span>
                <span class="info-value">' . htmlspecialchars($userData['full_name']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">' . htmlspecialchars($userData['email']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value">' . htmlspecialchars($userData['phone'] ?? 'N/A') . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Membership Tier:</span>
                <span class="info-value">' . strtoupper(htmlspecialchars($userData['membership_tier'] ?? 'Silver')) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Statement Date:</span>
                <span class="info-value">' . date('Y-m-d H:i:s') . '</span>
            </div>
        </div>
        
        <div class="bookings-section">
            <div class="section-title">Booking History</div>';
            
        if (empty($bookings)) {
            echo '<p style="text-align: center; color: #666; padding: 20px;">No bookings found.</p>';
        } else {
            $totalAmount = 0;
            $totalDiscount = 0;
            
            foreach ($bookings as $booking) {
                // ใช้ total_cost แทน total_amount (ตามโครงสร้างข้อมูลจริง)
                $amount = floatval($booking['total_cost'] ?? $booking['total_amount'] ?? 0);
                $discount = floatval($booking['discount_amount'] ?? $booking['discount'] ?? 0);
                $totalAmount += $amount;
                $totalDiscount += $discount;
                
                $statusClass = 'status-' . strtolower($booking['status'] ?? 'pending');
                
                echo '<div class="booking-item">
                    <div class="booking-header">
                        <span class="booking-id">Booking ID: ' . htmlspecialchars($booking['id']) . '</span>
                        <span class="booking-status ' . $statusClass . '">' . htmlspecialchars($booking['status'] ?? 'Pending') . '</span>
                    </div>
                    <div class="booking-details">
                        <div class="detail-item">
                            <span class="detail-label">Jet Model:</span>
                            <span>' . htmlspecialchars($booking['jet_model'] ?? 'N/A') . '</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Departure:</span>
                            <span>' . htmlspecialchars($booking['departure_location'] ?? 'N/A') . '</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Arrival:</span>
                            <span>' . htmlspecialchars($booking['arrival_location'] ?? 'N/A') . '</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date:</span>
                            <span>' . htmlspecialchars($booking['departure_date'] ?? 'N/A') . '</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Time:</span>
                            <span>' . htmlspecialchars($booking['departure_time'] ?? 'N/A') . '</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Passengers:</span>
                            <span>' . htmlspecialchars($booking['passengers'] ?? 'N/A') . '</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Flight Hours:</span>
                            <span>' . htmlspecialchars($booking['flight_hours'] ?? 'N/A') . '</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Amount:</span>
                            <span>฿' . number_format($amount, 2) . '</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Discount:</span>
                            <span>฿' . number_format($discount, 2) . '</span>
                        </div>
                    </div>
                </div>';
            }
            
            $netAmount = $totalAmount - $totalDiscount;
            
            echo '<div class="summary">
                <div class="summary-row">
                    <span>Total Bookings:</span>
                    <span>' . count($bookings) . '</span>
                </div>
                <div class="summary-row">
                    <span>Total Amount:</span>
                    <span>฿' . number_format($totalAmount, 2) . '</span>
                </div>
                <div class="summary-row">
                    <span>Total Discount:</span>
                    <span>฿' . number_format($totalDiscount, 2) . '</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Net Amount:</span>
                    <span>฿' . number_format($netAmount, 2) . '</span>
                </div>
            </div>';
        }
        
        echo '</div>
        
        <div class="footer">
            <p>Thank you for choosing Prestige Jets</p>
            <p>This is a computer-generated document. No signature required.</p>
            <p>Generated on ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';
        
        $html = ob_get_clean();
        return $html;
    }
    
    public function outputPDF($html, $filename = 'booking_statement.pdf') {
        // Add JavaScript for automatic PDF conversion
        $html = str_replace('</body>', '
        <script>
            // Auto-print to PDF when page loads
            window.onload = function() {
                // Set title for PDF
                document.title = "Prestige Jets - Booking Statement";
                
                // Automatically trigger print dialog
                setTimeout(function() {
                    window.print();
                }, 500);
            };
            
            // Override print styles
            const style = document.createElement("style");
            style.innerHTML = `
                @media print {
                    body { margin: 0; padding: 0; }
                    .container { 
                        max-width: none; 
                        margin: 0; 
                        padding: 20px; 
                        box-shadow: none;
                        border-radius: 0;
                    }
                    .header { page-break-inside: avoid; }
                    .booking-item { page-break-inside: avoid; }
                    .summary { page-break-inside: avoid; }
                }
            `;
            document.head.appendChild(style);
        </script>
        </body>', $html);
        
        return $html;
    }
} 