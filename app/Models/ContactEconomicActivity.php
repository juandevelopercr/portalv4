<?php

namespace App\Models;

use App\Models\Contact;
use App\Models\EconomicActivity;
use Illuminate\Database\Eloquent\Model;

class ContactEconomicActivity extends Model
{
    // Nombre de la tabla
    protected $table = 'contacts_economic_activities';

    // Deshabilitar timestamps (ya que la tabla no tiene created_at ni updated_at)
    public $timestamps = false;

    // Campos clave (relaciones)
    protected $fillable = [
        'contact_id',
        'economic_activity_id',
    ];

    /**
     * Relación con el modelo Contact.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * Relación con el modelo EconomicActivity.
     */
    public function economicActivity()
    {
        return $this->belongsTo(EconomicActivity::class, 'economic_activity_id');
    }
}
