<?php

namespace App\Http\Requests;

use App\Models\Affiliate;
use App\Models\AffiliateCorporate;
use App\Models\Affiliation;
use App\Models\AffiliationCorporate;
use App\Models\AffiliationDocument;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class StoreAffiliationDocumentWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización ya la resuelve el middleware de firma del webhook.
        return true;
    }

    /**
     * Este endpoint solo lo consume Integracorp (server-to-server), nunca un
     * navegador: si no envía "Accept: application/json" el comportamiento
     * por defecto de FormRequest sería redirigir (302) en vez de devolver
     * 422, rompiendo el contrato acordado con el emisor del webhook.
     */
    protected function failedValidation(ValidatorContract $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Payload inválido.',
            'errors' => $validator->errors(),
        ], 422));
    }

    public function rules(): array
    {
        return [
            'affiliation_type' => ['required', 'in:individual,corporate'],
            'affiliation_code' => ['required', 'string', 'max:191'],
            'document_type' => ['required', 'in:'.AffiliationDocument::TYPE_CERTIFICADO.','.AffiliationDocument::TYPE_CARNET],
            // El carnet es un documento por persona: obligatorio solo para
            // ese tipo (el certificado sigue siendo uno por afiliación).
            'affiliate_identification' => ['required_if:document_type,'.AffiliationDocument::TYPE_CARNET, 'nullable', 'string', 'max:191'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'checksum_sha256' => ['required', 'regex:/^[a-f0-9]{64}$/i'],
            'generated_at' => ['required', 'date'],
            'idempotency_key' => ['required', 'string', 'max:191'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (ValidatorContract $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->hasFile('file')) {
                return;
            }

            $actualChecksum = hash_file('sha256', $this->file('file')->getRealPath());

            if (! hash_equals(strtolower($this->input('checksum_sha256')), $actualChecksum)) {
                $validator->errors()->add('checksum_sha256', 'El checksum no coincide con el archivo recibido.');
            }

            if (! $this->affiliationExists()) {
                $validator->errors()->add('affiliation_code', 'No existe una afiliación con ese código y tipo.');

                return;
            }

            if ($this->input('document_type') === AffiliationDocument::TYPE_CARNET && ! $this->affiliateExists()) {
                $validator->errors()->add('affiliate_identification', 'No existe un afiliado con esa identificación en esta afiliación.');
            }
        });
    }

    public function affiliationExists(): bool
    {
        $code = $this->input('affiliation_code');

        return match ($this->input('affiliation_type')) {
            AffiliationDocument::KIND_INDIVIDUAL => Affiliation::where('code', $code)->exists(),
            AffiliationDocument::KIND_CORPORATE => AffiliationCorporate::where('code', $code)->exists(),
            default => false,
        };
    }

    /**
     * El afiliado debe pertenecer a la MISMA afiliación indicada en el
     * payload, no bastar con que exista en cualquier lugar del sistema.
     */
    public function affiliateExists(): bool
    {
        $code = $this->input('affiliation_code');
        $identification = $this->input('affiliate_identification');

        return match ($this->input('affiliation_type')) {
            AffiliationDocument::KIND_INDIVIDUAL => Affiliate::where('nro_identificacion', $identification)
                ->whereHas('affiliation', fn ($query) => $query->where('code', $code))
                ->exists(),
            AffiliationDocument::KIND_CORPORATE => AffiliateCorporate::where('nro_identificacion', $identification)
                ->whereHas('affiliationCorporate', fn ($query) => $query->where('code', $code))
                ->exists(),
            default => false,
        };
    }
}
