<?php

namespace App\Http\Livewire\Referensi;

use App\Models\Anggota_rombel;
use App\Models\Pd_keluar;
use Livewire\WithPagination;
use Livewire\Component;
use App\Models\Peserta_didik;
use App\Models\Rombongan_belajar;

class PesertaDidikKeluar extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search = '';
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function loadPerPage(){
        $this->resetPage();
    }
    public $sortby = 'nama';
    public $sortbydesc = 'ASC';
    public $per_page = 10;
    public $kordinator;
    public $peserta_didik_id;
    public $rombongan_belajar_id;
    public $rom;
    public $pd;

    public function render()
    {
        return view('livewire.referensi.peserta-didik-keluar', [
            'collection' => Peserta_didik::whereHas('pd_keluar', function($query){
                $query->where('sekolah_id', session('sekolah_id'));
                $query->where('semester_id', session('semester_aktif'));
            })->orderBy($this->sortby, $this->sortbydesc)
                ->when($this->search, function($ptk) {
                    $ptk->where('nama', 'ILIKE', '%' . $this->search . '%')
                    ->orWhere('nisn', 'ILIKE', '%' . $this->search . '%');
            })->paginate($this->per_page),
            'kordinator' => $this->getKordinator(),
            'breadcrumbs' => [
                ['link' => "/", 'name' => "Beranda"], ['link' => '#', 'name' => 'Referensi'], ['name' => "Data Peserta Didik Keluar"]
            ]
        ]);
    }
    public function getKordinator(){
        $this->kordinator = Rombongan_belajar::where('sekolah_id', session('sekolah_id'))->orderBy('nama')->get();
    }
    public function kembali($pd){
        // Anggota_rombel::onlyTrashed()->where('peserta_didik_id', $pd)->restore();
        Anggota_rombel::onlyTrashed('peserta_didik_id', $pd)->update(['rombongan_belajar_id' => $this->rom,]);
        Anggota_rombel::onlyTrashed('peserta_didik_id', $pd)->restore();
        Pd_keluar::where('peserta_didik_id', $pd)->delete();
        // dd($d);
        // if ($ar) {
        //     $ar->restore();
        // }
        // dd([$pd,$this->rom]);
    }
}
