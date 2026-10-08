<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiDocsController extends Controller
{
    /**
     * Display API Documentation & React Integration Explorer.
     */
    public function index()
    {
        $baseUrl = url('/');
        $apiEndpoints = [
            [
                'id' => 'beranda',
                'method' => 'GET',
                'url' => '/api/beranda',
                'name' => 'Data Beranda & Profil Kantor',
                'description' => 'Mengambil seluruh data gabungan (Profil Perusahaan, List Alumni, Komentar, dan Ringkasan Statistik) untuk Halaman Beranda Utama.',
                'auth' => false,
                'react_code' => "import React, { useEffect, useState } from 'react';

function BerandaPage() {
  const [berandaData, setBerandaData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('{$baseUrl}/api/beranda', {
      headers: { 'Accept': 'application/json' }
    })
      .then(res => res.json())
      .then(resData => {
        setBerandaData(resData.data);
        setLoading(false);
      })
      .catch(err => console.error('Error:', err));
  }, []);

  if (loading) return <div>Memuat data beranda...</div>;

  return (
    <div>
      <h1>{berandaData?.profil_kantor?.nama_perusahaan}</h1>
      <p>{berandaData?.profil_kantor?.deskripsi_singkat}</p>
      <h3>Total Alumni: {berandaData?.statistik?.total_alumni}</h3>
    </div>
  );
}

export default BerandaPage;",
            ],
            [
                'id' => 'alumni',
                'method' => 'GET',
                'url' => '/api/beranda/alumni',
                'name' => 'Daftar Alumni Magang',
                'description' => 'Mengambil daftar alumni magang (dapat menggunakan parameter query ?search=keyword untuk filter pencarian).',
                'auth' => false,
                'react_code' => "import React, { useEffect, useState } from 'react';

function AlumniList() {
  const [alumni, setAlumni] = useState([]);
  const [search, setSearch] = useState('');

  const loadAlumni = (query = '') => {
    fetch('{$baseUrl}/api/beranda/alumni?search=' + query, {
      headers: { 'Accept': 'application/json' }
    })
      .then(res => res.json())
      .then(resData => setAlumni(resData.data));
  };

  useEffect(() => {
    loadAlumni();
  }, []);

  return (
    <div>
      <input 
        type=\"text\" 
        placeholder=\"Cari nama atau instansi...\" 
        value={search} 
        onChange={(e) => { setSearch(e.target.value); loadAlumni(e.target.value); }} 
      />
      <ul>
        {alumni.map(item => (
          <li key={item.id}>
            <strong>{item.nama}</strong> - {item.asal_instansi} ({item.jurusan})
          </li>
        ))}
      </ul>
    </div>
  );
}

export default AlumniList;",
            ],
            [
                'id' => 'profil',
                'method' => 'GET',
                'url' => '/api/beranda/profil',
                'name' => 'Detail Profil Perusahaan',
                'description' => 'Mengambil informasi lengkap profil perusahaan (visi, misi, deskripsi, alamat, kontak).',
                'auth' => false,
                'react_code' => "import React, { useEffect, useState } from 'react';

function ProfilKantor() {
  const [profil, setProfil] = useState(null);

  useEffect(() => {
    fetch('{$baseUrl}/api/beranda/profil', {
      headers: { 'Accept': 'application/json' }
    })
      .then(res => res.json())
      .then(resData => setProfil(resData.data));
  }, []);

  return (
    <div>
      <h2>{profil?.nama_perusahaan}</h2>
      <p><strong>Visi:</strong> {profil?.visi}</p>
      <p><strong>Misi:</strong> {profil?.misi}</p>
      <p><strong>Email:</strong> {profil?.email}</p>
    </div>
  );
}

export default ProfilKantor;",
            ],
            [
                'id' => 'get_komentar',
                'method' => 'GET',
                'url' => '/api/komentar',
                'name' => 'Daftar Komentar Pengguna',
                'description' => 'Mengambil seluruh ulasan, masukan, dan rating bintang pengguna yang berstatus published.',
                'auth' => false,
                'react_code' => "import React, { useEffect, useState } from 'react';

function ListKomentar() {
  const [komentarList, setKomentarList] = useState([]);
  const [avgRating, setAvgRating] = useState(5.0);

  useEffect(() => {
    fetch('{$baseUrl}/api/komentar', {
      headers: { 'Accept': 'application/json' }
    })
      .then(res => res.json())
      .then(result => {
        setKomentarList(result.data);
        setAvgRating(result.avg_rating);
      });
  }, []);

  return (
    <div>
      <h2>Komentar & Ulasan Pengguna ({avgRating} ⭐)</h2>
      {komentarList.map(item => (
        <div key={item.id} style={{ border: '1px solid #ccc', padding: '1rem', marginBottom: '1rem' }}>
          <strong>{item.nama_pengguna}</strong> ({item.rating} Bintang)
          <p>\"{item.komentar}\"</p>
        </div>
      ))}
    </div>
  );
}

export default ListKomentar;",
            ],
            [
                'id' => 'post_komentar',
                'method' => 'POST',
                'url' => '/api/komentar',
                'name' => 'Kirim Komentar Baru (Protected - Wajib Login)',
                'description' => 'Mengirim ulasan, masukan, atau rating bintang baru dari pengguna aplikasi (memerlukan Header Authorization Bearer Token).',
                'auth' => true,
                'react_code' => "import React, { useState } from 'react';

function FormKomentar() {
  const [rating, setRating] = useState(5);
  const [komentar, setKomentar] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    const token = localStorage.getItem('token');

    const response = await fetch('{$baseUrl}/api/komentar', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer \${token}`
      },
      body: JSON.stringify({
        rating: Number(rating),
        komentar: komentar
      })
    });
    const result = await response.json();
    if (result.status === 'success') {
      alert('Terima kasih! Komentar Anda berhasil dikirim.');
      setKomentar('');
    } else {
      alert('Gagal mengirim komentar: ' + result.message);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <select onChange={e => setRating(e.target.value)}>
        <option value=\"5\">5 Bintang</option>
        <option value=\"4\">4 Bintang</option>
        <option value=\"3\">3 Bintang</option>
      </select>
      <textarea placeholder=\"Komentar / Masukan...\" onChange={e => setKomentar(e.target.value)} required />
      <button type=\"submit\">Kirim Komentar</button>
    </form>
  );
}

export default FormKomentar;",
            ],
            [
                'id' => 'get_aktivitas',
                'method' => 'GET',
                'url' => '/api/aktivitas',
                'name' => 'Daftar Aktivitas Magang (Input Admin)',
                'description' => 'Mengambil seluruh daftar agenda, jurnal, dan kegiatan magang yang di-input oleh admin.',
                'auth' => false,
                'react_code' => "import React, { useEffect, useState } from 'react';

function ListAktivitas() {
  const [aktivitas, setAktivitas] = useState([]);

  useEffect(() => {
    fetch('{$baseUrl}/api/aktivitas', {
      headers: { 'Accept': 'application/json' }
    })
      .then(res => res.json())
      .then(result => setAktivitas(result.data));
  }, []);

  return (
    <div>
      <h2>Aktivitas Magang</h2>
      {aktivitas.map(item => (
        <div key={item.id} style={{ border: '1px solid #ccc', margin: '1rem 0', padding: '1rem' }}>
          <h3>{item.judul}</h3>
          <p><small>Oleh: {item.user?.name} | Tanggal: {item.tanggal}</small></p>
          <p>{item.deskripsi}</p>
          <span>💬 {item.komentar_count} Komentar</span>
        </div>
      ))}
    </div>
  );
}

export default ListAktivitas;",
            ],
            [
                'id' => 'get_detail_aktivitas',
                'method' => 'GET',
                'url' => '/api/aktivitas/1',
                'name' => 'Detail Aktivitas Magang & Daftar Komentar',
                'description' => 'Mengambil detail rinci aktivitas magang beserta seluruh komentar yang diberikan pengguna.',
                'auth' => false,
                'react_code' => "import React, { useEffect, useState } from 'react';

function DetailAktivitas({ id = 1 }) {
  const [data, setData] = useState(null);

  useEffect(() => {
    fetch(`{$baseUrl}/api/aktivitas/\${id}`, {
      headers: { 'Accept': 'application/json' }
    })
      .then(res => res.json())
      .then(result => setData(result.data));
  }, [id]);

  if (!data) return <div>Memuat detail aktivitas...</div>;

  return (
    <div>
      <h1>{data.judul}</h1>
      <p>Tanggal: {data.tanggal} | Lokasi: {data.lokasi}</p>
      <p>{data.deskripsi}</p>
      <h4>Komentar Pengguna ({data.komentar?.length}):</h4>
      {data.komentar?.map(k => (
        <div key={k.id} style={{ background: '#f5f5f5', padding: '0.5rem', marginBottom: '0.5rem' }}>
          <strong>{k.user?.name}:</strong> {k.komentar}
        </div>
      ))}
    </div>
  );
}

export default DetailAktivitas;",
            ],
            [
                'id' => 'post_komentar_aktivitas',
                'method' => 'POST',
                'url' => '/api/aktivitas/1/komentar',
                'name' => 'Kirim Komentar Pengguna pada Aktivitas (Protected)',
                'description' => 'Pengguna mengirimkan komentar ulasan pada aktivitas magang yang di-input admin (Wajib Login / Bearer Token).',
                'auth' => true,
                'react_code' => "import React, { useState } from 'react';

function CommentForm({ aktivitasId = 1 }) {
  const [komentar, setKomentar] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    const token = localStorage.getItem('token');

    const res = await fetch(`{$baseUrl}/api/aktivitas/\${aktivitasId}/komentar`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer \${token}`
      },
      body: JSON.stringify({ komentar })
    });
    const result = await res.json();
    if (result.status === 'success') {
      alert('Komentar berhasil dikirim!');
      setKomentar('');
    } else {
      alert('Gagal: ' + result.message);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <textarea 
        placeholder=\"Tulis komentar Anda pada aktivitas ini...\" 
        value={komentar}
        onChange={e => setKomentar(e.target.value)} 
        required 
      />
      <button type=\"submit\">Kirim Komentar</button>
    </form>
  );
}

export default CommentForm;",
            ],
            [
                'id' => 'login',
                'method' => 'POST',
                'url' => '/api/login',
                'name' => 'Otentikasi Login API',
                'description' => 'Verifikasi email & password, mengembalikan objek User dan Bearer Token Sanctum.',
                'auth' => false,
                'react_code' => "import React, { useState } from 'react';

function LoginForm() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleLogin = async (e) => {
    e.preventDefault();
    const res = await fetch('{$baseUrl}/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email, password })
    });
    const result = await res.json();
    if (result.status === 'success') {
      localStorage.setItem('token', result.data.access_token);
      alert('Login Berhasil! Token disimpan.');
    } else {
      alert('Login Gagal: ' + result.message);
    }
  };

  return (
    <form onSubmit={handleLogin}>
      <input type=\"email\" onChange={e => setEmail(e.target.value)} placeholder=\"Email\" required />
      <input type=\"password\" onChange={e => setPassword(e.target.value)} placeholder=\"Password\" required />
      <button type=\"submit\">Masuk</button>
    </form>
  );
}

export default LoginForm;",
            ],
            [
                'id' => 'register',
                'method' => 'POST',
                'url' => '/api/register',
                'name' => 'Pendaftaran Akun Baru',
                'description' => 'Mendaftarkan akun pengguna baru dan langsung menerbitkan Bearer Token Sanctum.',
                'auth' => false,
                'react_code' => "import React, { useState } from 'react';

function RegisterForm() {
  const [formData, setFormData] = useState({ name: '', email: '', password: '' });

  const handleRegister = async (e) => {
    e.preventDefault();
    const res = await fetch('{$baseUrl}/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(formData)
    });
    const result = await res.json();
    if (result.status === 'success') {
      localStorage.setItem('token', result.data.access_token);
      alert('Registrasi Berhasil!');
    }
  };

  return (
    <form onSubmit={handleRegister}>
      <input type=\"text\" onChange={e => setFormData({...formData, name: e.target.value})} placeholder=\"Nama Lengkap\" required />
      <input type=\"email\" onChange={e => setFormData({...formData, email: e.target.value})} placeholder=\"Email\" required />
      <input type=\"password\" onChange={e => setFormData({...formData, password: e.target.value})} placeholder=\"Password\" required />
      <button type=\"submit\">Daftar</button>
    </form>
  );
}

export default RegisterForm;",
            ],
            [
                'id' => 'me',
                'method' => 'GET',
                'url' => '/api/me',
                'name' => 'Get Profil User Login (Protected)',
                'description' => 'Mengambil profil pengguna yang sedang login aktif (memerlukan Header Authorization Bearer Token).',
                'auth' => true,
                'react_code' => "import React, { useEffect, useState } from 'react';

function UserProfile() {
  const [user, setUser] = useState(null);

  useEffect(() => {
    const token = localStorage.getItem('token');
    fetch('{$baseUrl}/api/me', {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer \${token}`
      }
    })
      .then(res => res.json())
      .then(result => setUser(result.data));
  }, []);

  return (
    <div>
      <h2>Halo, {user?.name}</h2>
      <p>Email: {user?.email}</p>
      <p>Role: {user?.role}</p>
    </div>
  );
}

export default UserProfile;",
            ],
            [
                'id' => 'logout',
                'method' => 'POST',
                'url' => '/api/logout',
                'name' => 'Logout User (Protected)',
                'description' => 'Menghapus token akses Sanctum yang sedang digunakan user.',
                'auth' => true,
                'react_code' => "import React from 'react';

function LogoutButton() {
  const handleLogout = async () => {
    const token = localStorage.getItem('token');
    await fetch('{$baseUrl}/api/logout', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer \${token}`
      }
    });
    localStorage.removeItem('token');
    alert('Anda telah berhasil Logout');
  };

  return (
    <button onClick={handleLogout}>Logout</button>
  );
}

export default LogoutButton;",
            ],
        ];

        return view('api_docs.index', compact('baseUrl', 'apiEndpoints'));
    }
}
