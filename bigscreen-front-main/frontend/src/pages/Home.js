import React from 'react';
import { useNavigate } from 'react-router-dom';


function Home() {
  const navigate = useNavigate();

  const handleLogout = () => {
    // Handle logout logic here
    navigate('/');
  };

  return (
    
      
    <div style={{ padding: '20px' }}>
      <h1>Welcome to Home Page</h1>
      <button onClick={handleLogout} style={{ width: 'auto' }}>
        Logout
      </button>
    </div>
    
  );
}

export default Home;
