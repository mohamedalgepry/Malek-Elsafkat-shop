<?php
/**
 * Order Controller
 */
class OrderController {
    private $orderModel;
    
    public function __construct() {
        $this->orderModel = new Order();
    }
    
    /**
     * Display order tracking page
     */
    public function track() {
        $invoiceNumber = $_GET['invoice'] ?? null;
        $order = null;
        
        if ($invoiceNumber) {
            $order = $this->orderModel->getByInvoice($invoiceNumber);
        }
        
        require __DIR__ . '/../views/pages/track-order.php';
    }
    
    /**
     * Search order by invoice number
     */
    public function search() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/track-order');
            return;
        }
        
        $invoiceNumber = $_POST['invoice_number'] ?? '';
        
        if (empty($invoiceNumber)) {
            $_SESSION['error'] = 'يرجى إدخال رقم الفاتورة';
            redirect('/track-order');
            return;
        }
        
        redirect('/track-order?invoice=' . urlencode($invoiceNumber));
    }
}
