function downloadPDF() {
    // Show loading message
    var btn = document.querySelector(".download-btn");
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังสร้าง PDF...';
    btn.disabled = true;
    
    // Hide the download button before capturing
    btn.style.display = 'none';
    
    // Get container element and ensure it's fully visible
    var container = document.querySelector('.container');
    var originalWidth = container.style.width;
    var originalMaxWidth = container.style.maxWidth;
    
    // Temporarily adjust container for full capture
    container.style.width = 'auto';
    container.style.maxWidth = 'none';
    
    // Use html2canvas to capture the container
    html2canvas(container, {
        scale: 1.5,
        useCORS: true,
        backgroundColor: '#ffffff',
        width: container.scrollWidth,
        height: container.scrollHeight,
        allowTaint: true,
        foreignObjectRendering: false,
        logging: false
    }).then(function(canvas) {
        // Restore original styles
        container.style.width = originalWidth;
        container.style.maxWidth = originalMaxWidth;
        // Show the button again
        btn.style.display = 'block';
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        var imgData = canvas.toDataURL('image/png', 1.0);
        
        // Calculate dimensions for A4 PDF
        var pdfWidth = 210; // A4 width in mm
        var pdfHeight = 297; // A4 height in mm
        var margin = 10; // 10mm margin
        var availableWidth = pdfWidth - (margin * 2);
        var availableHeight = pdfHeight - (margin * 2);
        
        // Calculate image dimensions to fit within available space
        var imgWidth = availableWidth;
        var imgHeight = (canvas.height * availableWidth) / canvas.width;
        
        var pdf = new window.jspdf.jsPDF('p', 'mm', 'a4');
        var position = margin;
        var heightLeft = imgHeight;
        
        // Add first page
        pdf.addImage(imgData, 'PNG', margin, position, imgWidth, imgHeight);
        heightLeft -= availableHeight;
        
        // Add additional pages if needed
        while (heightLeft >= 0) {
            position = heightLeft - imgHeight + margin;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', margin, position, imgWidth, imgHeight);
            heightLeft -= availableHeight;
        }
        
        // Generate filename
        var today = new Date();
        var year = today.getFullYear();
        var month = String(today.getMonth() + 1).padStart(2, '0');
        var day = String(today.getDate()).padStart(2, '0');
        var dateStr = year + '-' + month + '-' + day;
        var filename = 'prestige_jets_statement_' + dateStr + '.pdf';
        
        // Download the PDF
        pdf.save(filename);
    }).catch(function(error) {
        console.error('Error generating PDF:', error);
        btn.style.display = 'block';
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('เกิดข้อผิดพลาดในการสร้าง PDF กรุณาลองใหม่อีกครั้ง');
    });
} 