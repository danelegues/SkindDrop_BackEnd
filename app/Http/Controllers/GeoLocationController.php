<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class GeoLocationController extends Controller
{
    public function detectLanguage(Request $request)
    {
        $ip = $request->ip();
        
        // Usar un servicio de geolocalización gratuito
        $response = file_get_contents("http://ip-api.com/json/{$ip}");
        $data = json_decode($response);
        
        if ($data && isset($data->countryCode)) {
            $language = $this->getLanguageByCountry($data->countryCode);
            return response()->json(['language' => $language]);
        }
        
        return response()->json(['language' => 'en']); // Idioma por defecto
    }

    private function getLanguageByCountry($countryCode)
    {
        $spanishSpeakingCountries = [
            'ES', 'MX', 'AR', 'CL', 'CO', 'PE', 'VE', 'EC', 
            'GT', 'CU', 'BO', 'DO', 'HN', 'PY', 'SV', 'NI', 
            'CR', 'PA', 'UY', 'GQ'
        ];
        
        return in_array($countryCode, $spanishSpeakingCountries) ? 'es' : 'en';
    }
}
