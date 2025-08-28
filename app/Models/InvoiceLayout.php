<?php

namespace App\Models;

use App\Models\Business;
use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLayout extends TenantModel
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'header_text',
    'invoice_no_prefix',
    'quotation_no_prefix',
    'invoice_heading',
    'sub_heading_line1',
    'quotation_heading',
    'footer_text',
    'is_default',
    'business_id',
    'design',
    'show_qr',
  ];

  /**
   * Get the business that owns the invoice layout.
   */
  public function business()
  {
    return $this->belongsTo(Business::class);
  }
}
