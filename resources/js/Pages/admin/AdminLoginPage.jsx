import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { api } from '../../api';

export default function AdminLoginPage() {
  const navigate = useNavigate();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const { data } = await api.post('admin/login', { username, password });
      if (data.success) navigate('/admin');
      else setError(data.message || 'Invalid credentials');
    } catch (err) {
      setError(err.response?.data?.message || 'Error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-sm mx-auto card bg-base-100 shadow-lg mt-12">
      <div className="card-body">
        <h1 className="card-title">Admin login</h1>
        <form onSubmit={handleSubmit} className="space-y-5">
          {error && <div className="alert alert-error text-sm">{error}</div>}
          <label className="form-field w-full">
            <span className="form-label">Usuari</span>
            <input type="text" className="input input-bordered w-full" value={username} onChange={(e) => setUsername(e.target.value)} required />
          </label>
          <label className="form-field w-full">
            <span className="form-label">Contrasenya</span>
            <input type="password" className="input input-bordered w-full" value={password} onChange={(e) => setPassword(e.target.value)} required />
          </label>
          <button type="submit" className="btn btn-primary" disabled={loading}>
            {loading ? '...' : 'Entrar'}
          </button>
        </form>
      </div>
    </div>
  );
}
