<?php

namespace App\Http\Livewire\Referensi;

use App\Models\Anggota_rombel;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Component;
use App\Models\Peserta_didik;
use App\Models\Rombongan_belajar;
use App\Models\Jurusan_sp;
use App\Models\Pekerjaan;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;


class PesertaDidikAktif extends Component
{
    use WithPagination, WithFileUploads, LivewireAlert;
    protected $paginationTheme = 'bootstrap';
    public $search = '';

    private function loggedUser(){
        return auth()->user();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function loadPerPage(){
        $this->resetPage();
    }
    protected $listeners = [
        'proses_sinkron',
        'show_progress'
    ];
    public $sortby = 'nama';
    public $sortbydesc = 'ASC';
    public $per_page = 10;
    public $rombongan_belajar_id;
    public $data = 'pd';
    public $pd_id,
        $pd,
        $nama,
        $nis,
        $nisn,
        $nik,
        $jenis_kelamin,
        $tempat_tanggal_lahir,
        $agama,
        $status,
        $anak_ke,
        $alamat,
        $rt_rw,
        $rt,
        $rw,
        $desa_kelurahan,
        $kecamatan,
        $kode_pos,
        $no_hp,
        $sekolah_asal,
        $diterima_kelas,
        $diterima,
        $email,
        $nama_ayah,
        $kerja_ayah,
        $nama_ibu,
        $kerja_ibu,
        $nama_wali,
        $alamat_wali,
        $telp_wali,
        $kerja_wali;
    public $filter_tingkat;
    public $filter_jurusan;
    public $filter_rombel;
    public $result = [];
    public $file_path;
    public $file_excel;
    public $imported_data = [];
    public $kordinator = [];
    public $nama_kelas;

    public function mount(){
        if($this->loggedUser()->hasRole('wali', session('semester_id'))){
            $this->rombongan_belajar_id = $this->loggedUser()->guru->rombongan_belajar->rombongan_belajar_id;
        } else {
            $this->rombongan_belajar_id = NULL;
        }
    }
    public function render(){
        return view('livewire.referensi.peserta-didik-aktif', [
            'collection' => Peserta_didik::whereHas('anggota_rombel', $this->kondisi())
            ->with(['anggota_rombel' => $this->kondisi()])
            ->orderBy($this->sortby, $this->sortbydesc)
            ->when($this->search, function($query) {
                $query->where('nama', 'ILIKE', '%' . $this->search . '%');
                $query->whereHas('anggota_rombel', $this->kondisi());
                $query->orWhere('nisn', 'ILIKE', '%' . $this->search . '%');
                $query->whereHas('anggota_rombel', $this->kondisi());
                $query->orWhereHas('agama', function($query){
                    $query->where('nama', 'ILIKE', '%' . $this->search . '%');
                });
                $query->whereHas('anggota_rombel', $this->kondisi());
                $query->orWhere('tempat_lahir', 'ILIKE', '%' . $this->search . '%');
                $query->whereHas('anggota_rombel', $this->kondisi());
            })->when($this->filter_tingkat, function($query){
                $query->whereHas('anggota_rombel', function($query){
                    $query->wherehas('rombongan_belajar', function($query){
                        $query->where('tingkat', $this->filter_tingkat);
                    });
                });
            })->when($this->filter_jurusan, function($query){
                $query->whereHas('anggota_rombel', function($query){
                    $query->wherehas('rombongan_belajar', function($query){
                        $query->where('jurusan_sp_id', $this->filter_jurusan);
                    });
                });
            })->when($this->filter_rombel, function($query){
                $query->whereHas('anggota_rombel', function($query){
                    $query->where('rombongan_belajar_id', $this->filter_rombel);
                });
            })->paginate($this->per_page),
            'pekerjaan_wali' => Pekerjaan::get(),
            'breadcrumbs' => [
                ['link' => "/", 'name' => "Beranda"], ['link' => '#', 'name' => 'Referensi'], ['name' => "Data Peserta Didik Aktif"]
            ],
            'tombol_add' => [
                'wire' => 'addModal',
                'color' => 'primary',
                'text' => 'Tambah Data',
            ],
        ]);
    }
    public function addModal(){
        $this->emit('showModal');
    }
    public function updatedFileExcel(){
        $this->validate(
            [
                'file_excel' => 'required|mimes:xlsx',
            ],
            [
                'file_excel.required' => 'File Excel tidak boleh kosong',
                'file_excel.mimes' => 'File harus berupa file dengan tipe: xlsx.',
            ]
        );
        $this->file_path = $this->file_excel->store('files', 'public');
        $this->imported_data();
    }
    private function imported_data(){
        $imported_data = (new FastExcel)->import(storage_path('/app/public/'.$this->file_path));
        $collection = collect($imported_data);
        $multiplied = $collection->map(function ($items, $key) {
            foreach($items as $k => $v){
                $k = str_replace('.','',$k);
                $k = str_replace(' ','_',$k);
                $k = str_replace('/','_',$k);
                $k = strtolower($k);
                $item[$k] = $v;
            }
            return $item;
        });
        foreach($multiplied->all() as $urut => $data){
            $this->nama[$urut] = $data['nama'];
            // $this->nuptk[$urut] = $data['nuptk'];
            // $this->nip[$urut] = $data['nip'];
            $this->nik[$urut] = $data['nik'];
            $this->jenis_kelamin[$urut] = $data['jenis_kelamin'];
            // $this->tempat_lahir[$urut] = $data['tempat_lahir'];
            // $this->tanggal_lahir[$urut] = (is_object($data['tanggal_lahir'])) ? $data['tanggal_lahir']->format('Y-m-d') : now()->format('Y-m-d');
            // $this->agama[$urut] = $data['agama'];
            $this->alamat[$urut] = $data['alamat'];
            $this->rt[$urut] = $data['rt'];
            $this->rw[$urut] = $data['rw'];
            $this->desa_kelurahan[$urut] = $data['desa_kelurahan'];
            $this->kecamatan[$urut] = $data['kecamatan'];
            $this->kordinator[$urut] = $data['kordinator'];
            $this->no_hp[$urut] = $data['no_hp'];
            // $this->email[$urut] = $data['email'];
        }
        $this->imported_data = $multiplied->all();
    }
    public function store(){
        $this->emit('show-tooltip');
        //$this->imported_data();
        $this->validate(
            [
                'nama.*' => 'required',
                'nik.*' => 'required|numeric|digits:4|unique:peserta_didik,nik',
                'email.*' => 'required|unique:peserta_didik,email',
            ],
            [
                'nama.*.required' => 'Nama tidak boleh kosong!',
                'nik.*.required' => 'NIK tidak boleh kosong!',
                'nik.*.numeric' => 'NIK harus berupa angka!',
                'nik.*.digits' => 'NIK harus 4 digit!',
                'email.*.required' => 'Email tidak boleh kosong!',
                'email.*.unique' => 'Email sudah terdaftar!',
                'nik.*.unique' => 'NIK sudah terdaftar!',
            ]
        );
        foreach($this->nama as $urut => $nama){
            // $agama = Agama::where('nama', $this->agama[$urut])->first();
            $kordinator = Rombongan_belajar::where('nama', $this->kordinator[$urut])->first();
            // $agama = '1';
            $anggota_rombel_id = Str::uuid();
            $peserta_didik_id = Str::uuid();
            if($kordinator){
                Peserta_didik::updateOrcreate(
                    [
                        'nik' => $this->nik[$urut],
                    ],
                    [
                        'peserta_didik_id' => $peserta_didik_id,
                        'sekolah_id' => session('sekolah_id'),
                        // 'status_kepegawaian_id' => 0,
                        // 'kode_wilayah' => null,
                        'nama' => $nama,
                        // 'nuptk' => $this->nuptk[$urut],
                        // 'nuptk' => null,
                        // 'nip' => null,
                        // 'nip' => $this->nip[$urut],
                        'jenis_kelamin' => $this->jenis_kelamin[$urut],
                        // 'tanggal_lahir' => null,
                        // 'tempat_lahir' => null,
                        // 'tempat_lahir' => $this->tempat_lahir[$urut],
                        // 'tanggal_lahir' => $this->tanggal_lahir[$urut],
                        'agama_id' => 1,
                        'alamat' => $this->alamat[$urut],
                        'rt' => $this->rt[$urut],
                        'rw' => $this->rw[$urut],
                        'desa_kelurahan' => $this->desa_kelurahan[$urut],
                        'kecamatan' => $this->kecamatan[$urut],
                        // 'kode_pos' => $this->kodepos[$urut],
                        // 'kode_pos' => null,
                        'no_telp' => $this->no_hp[$urut],
                        // 'email' => $this->email[$urut],
                        'email' => null,
                        // 'jenis_ptk_id' => 4,
                        'last_sync' => now(),
                    ]
                );
                Anggota_rombel::updateOrCreate(
                    [
                        'anggota_rombel_id' => $anggota_rombel_id,
                        'sekolah_id'        => session('sekolah_id'),
                        'semester_id'       => '20241',
                        'rombongan_belajar_id'  => $kordinator->rombongan_belajar_id,
                        'peserta_didik_id'  => $peserta_didik_id,
                        'last_sync' => now(),
                    ]
                );
            }
        }
        $this->reset(['imported_data']);
        $this->reset(['nama','nik', 'jenis_kelamin', 'alamat', 'rt', 'rw', 'desa_kelurahan', 'kecamatan', 'kode_pos', 'no_hp', 'email']);
        $this->emit('close-modal');
        $this->alert('success', 'Berhasil', [
            'text' => 'Data Alumni berhasil disimpan'
        ]);
    }
    public function kondisi(){
        return function($query){
            $query->where('sekolah_id', session('sekolah_id'));
            $query->where('semester_id', session('semester_aktif'));
            if($this->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', $this->rombongan_belajar_id);
            } else {
                $query->whereHas('rombongan_belajar', function($query){
                    $query->where('jenis_rombel', 1);
                });
            }
            $query->with(['rombongan_belajar']);
        };
    }
    public function getID($pd_id){
        $this->pd_id = $pd_id;
        $this->pd = Peserta_didik::find($this->pd_id);
        $this->nama = $this->pd->nama;
        $this->nis = $this->pd->no_induk;
        $this->nisn = $this->pd->nisn;
        $this->nik = $this->pd->nik;
        $this->jenis_kelamin = $this->pd->jenis_kelamin;
        $this->tempat_tanggal_lahir = $this->pd->tempat_lahir.', '.$this->pd->tanggal_lahir;
        $this->agama = ($this->pd->agama) ? $this->pd->agama->nama : '-';
        $this->status = $this->pd->status;
        $this->anak_ke = $this->pd->anak_ke;
        $this->alamat = $this->pd->alamat;
        $this->rt_rw = $this->pd->rt.'/'.$this->pd->rw;
        $this->desa_kelurahan = $this->pd->desa_kelurahan;
        $this->kecamatan = $this->pd->kecamatan;
        $this->kode_pos = $this->pd->kode_pos;
        $this->no_hp = $this->pd->no_hp;
        $this->sekolah_asal = $this->pd->sekolah_asal;
        $this->diterima_kelas = $this->pd->diterima_kelas;
        $this->diterima = $this->pd->diterima;
        $this->email = ($this->pd->user) ? $this->pd->user->email : $this->pd->email;
        $this->nama_ayah = $this->pd->nama_ayah;
        $this->kerja_ayah = $this->pd->kerja_ayah;
        $this->nama_ibu = $this->pd->nama_ibu;
        $this->kerja_ibu = $this->pd->kerja_ibu;
        $this->nama_wali = $this->pd->nama_wali;
        $this->alamat_wali = $this->pd->alamat_wali;
        $this->telp_wali = $this->pd->telp_wali;
        $this->kerja_wali = $this->pd->kerja_wali;
        $this->emit('show-modal');
    }
    public function updatedEmail(){
        $validation = ($this->pd->user) ? ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->pd->user->user_id, 'user_id')] : ['required', 'email', 'max:255', Rule::unique('users')];
        $this->validate(
            [
                'email' => $validation,
            ],
            [
                'email.required' => 'Email tidak boleh kosong!',
                'email.email' => 'Email tidak valid!',
                'email.unique' => 'Email sudah terdaftar di Database!',
            ]
        );
    }
    public function perbaharui(){
        $validation = ($this->pd->user) ? ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->pd->user->user_id, 'user_id')] : ['required', 'email', 'max:255', Rule::unique('users')];
        $this->validate(
            [
                'email' => $validation,
            ],
            [
                'email.required' => 'Email tidak boleh kosong!',
                'email.email' => 'Email tidak valid!',
                'email.unique' => 'Email sudah terdaftar di Database!',
            ]
        );
        $this->pd->status = $this->status;
        $this->pd->anak_ke = $this->anak_ke;
        $this->pd->diterima_kelas = $this->diterima_kelas;
        $this->pd->email = $this->email;
        $this->pd->nama_wali = $this->nama_wali;
        $this->pd->alamat_wali = $this->alamat_wali;
        $this->pd->telp_wali = $this->telp_wali;
        $this->pd->kerja_wali = $this->kerja_wali;
        if($this->pd->save()){
            if($this->pd->user){
                $this->pd->user->email = $this->email;
                $this->pd->user->save();
            } else {
                $role = Role::where('name', 'siswa')->first();
                $new_password = strtolower(Str::random(8));
                $user = User::create([
                    'name' => $this->pd->nama,
                    'email' => $this->email,
                    'nisn'	=> $this->nisn,
                    'password' => bcrypt($new_password),
                    'last_sync'	=> now(),
                    'sekolah_id'	=> session('sekolah_id'),
                    'password_dapo'	=> md5($new_password),
                    'peserta_didik_id'	=> $this->pd_id,
                    'default_password' => $new_password,
                ]);
                if(!$user->hasRole($role, session('semester_id'))){
                    $user->attachRole($role, session('semester_id'));
                }
            }
            $this->alert('success', 'Berhasil', [
                'html' => 'Data Peserta Didik berhasil diperbaharui!',
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'onConfirmed' => 'pembelajaranTersimpan',
                'allowOutsideClick' => false,
                'toast' => false,
            ]);
        } else {
            $this->alert('error', 'Pembelajaran gagal di reset. Coba beberapa saat lagi!', [
                'html' => 'Data Peserta Didik gagal diperbaharui.<br>Silahkan coba beberapa saat lagi!',
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'onConfirmed' => 'confirmed',
                'allowOutsideClick' => false,
                'toast' => false,
            ]);
        }
        $this->emit('close-modal');
    }
    public function tutup(){
        $this->reset(['pd_id', 'pd', 'nama', 'nis', 'nisn', 'nik', 'jenis_kelamin', 'tempat_tanggal_lahir', 'agama', 'status', 'anak_ke', 'alamat', 'rt_rw', 'desa_kelurahan', 'kecamatan', 'kode_pos', 'no_hp', 'sekolah_asal', 'diterima_kelas', 'diterima', 'email', 'nama_ayah', 'kerja_ayah', 'nama_ibu', 'kerja_ibu', 'nama_wali', 'alamat_wali', 'telp_wali', 'kerja_wali']);
    }
    public function syncPD($pd_id = NULL, $nama = NULL){
        $data_sync = [
            'peserta_didik_id' => ($pd_id) ? $pd_id : $this->pd_id,
            'sekolah_id'		=> session('sekolah_id'),
        ];
        $response = Http::withHeaders([
            'x-api-key' => session('sekolah_id'),
        ])->withBasicAuth('admin', '1234')->asForm()->post('http://app.erapor-smk.net/api/dapodik/diterima-dikelas', $data_sync);
        if($response->status() == 200){
            $data = $response->object();
            $diterima_kelas = '';
            if($pd_id){
                if($data->data){
                    Peserta_didik::where('peserta_didik_id', $pd_id)->whereNull('diterima_kelas')->update(['diterima_kelas' => $data->data->nama]);
                    $diterima_kelas = $data->data->nama;
                }
            } else {
                $this->diterima_kelas = ($data->data) ? $data->data->nama : '';
            }
            $this->result[$nama] = $diterima_kelas;
        }
    }
    private function url_server($server, $ep){
        return config('erapor.'.$server).$ep;
    }
    public function updatedFilterTingkat(){
        $this->reset(['filter_jurusan', 'filter_rombel']);
        if($this->filter_tingkat){
            $data_jurusan = Jurusan_sp::whereHas('rombongan_belajar', function($query){
                $query->where('tingkat', $this->filter_tingkat);
            })->where('sekolah_id', session('sekolah_id'))->get();
            $this->dispatchBrowserEvent('data_jurusan', ['data_jurusan' => $data_jurusan]);
        }
    }
    public function updatedFilterJurusan(){
        $this->reset(['filter_rombel']);
        if($this->filter_jurusan){
            $data_rombel = Rombongan_belajar::where('jurusan_sp_id', $this->filter_jurusan)->where('tingkat', $this->filter_tingkat)->get();
            $this->dispatchBrowserEvent('data_rombel', ['data_rombel' => $data_rombel]);
        }
    }
    public function updatedFilterRombel(){
        $this->rombongan_belajar_id = $this->filter_rombel;
    }
    public function sinkronisasi(){
        $this->reset(['result']);
        $data_siswa = Peserta_didik::whereHas('anggota_rombel', $this->kondisi())->select('peserta_didik_id', 'nama')->orderBy('nama')->get();
        foreach($data_siswa as $siswa){
            $this->syncPD($siswa->peserta_didik_id, $siswa->nama);
        }
        $this->emit('progress');
    }
    public function getRombel(){
        $find = Rombongan_belajar::find($this->rombongan_belajar_id);
        $this->nama_kelas = $find->nama;
        $this->guru_pengajar = Guru::where('sekolah_id', session('sekolah_id'))->orderBy('nama')->get();
    }
}
