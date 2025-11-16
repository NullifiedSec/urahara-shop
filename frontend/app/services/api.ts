/**
 * API Service Layer
 * 
 * Centralized API client for making requests to the backend.
 * Handles authentication, error handling, and request/response transformation.
 */

const API_BASE_URL = process.env.NUXT_PUBLIC_API_URL || 'http://localhost:8000/api'

/**
 * Base fetch wrapper with error handling
 */
async function apiRequest<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const url = `${API_BASE_URL}${endpoint}`
  
  // Get auth token from storage (implement based on your auth solution)
  const token = useCookie('auth_token').value
  
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...options.headers,
  }
  
  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }

  try {
    const response = await fetch(url, {
      ...options,
      headers,
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'An error occurred' }))
      throw new Error(error.message || `HTTP error! status: ${response.status}`)
    }

    return await response.json()
  } catch (error) {
    console.error('API request failed:', error)
    throw error
  }
}

/**
 * Product API methods
 */
export const productApi = {
  /**
   * Get all products with optional filters
   */
  getAll(params?: {
    category_id?: number
    search?: string
    featured?: boolean
    per_page?: number
  }) {
    const queryParams = new URLSearchParams()
    if (params?.category_id) queryParams.append('category_id', params.category_id.toString())
    if (params?.search) queryParams.append('search', params.search)
    if (params?.featured) queryParams.append('featured', 'true')
    if (params?.per_page) queryParams.append('per_page', params.per_page.toString())
    
    const query = queryParams.toString()
    return apiRequest(`/products${query ? `?${query}` : ''}`)
  },

  /**
   * Get a single product by ID
   */
  getById(id: number) {
    return apiRequest(`/products/${id}`)
  },
}

/**
 * Category API methods
 */
export const categoryApi = {
  /**
   * Get all categories
   */
  getAll(params?: { with_products?: boolean }) {
    const queryParams = new URLSearchParams()
    if (params?.with_products) queryParams.append('with_products', 'true')
    
    const query = queryParams.toString()
    return apiRequest(`/categories${query ? `?${query}` : ''}`)
  },

  /**
   * Get a single category by ID
   */
  getById(id: number, params?: { with_products?: boolean }) {
    const queryParams = new URLSearchParams()
    if (params?.with_products) queryParams.append('with_products', 'true')
    
    const query = queryParams.toString()
    return apiRequest(`/categories/${id}${query ? `?${query}` : ''}`)
  },
}

/**
 * Cart API methods (requires authentication)
 */
export const cartApi = {
  /**
   * Get user's cart
   */
  get() {
    return apiRequest('/cart')
  },

  /**
   * Add item to cart
   */
  addItem(productId: number, quantity: number) {
    return apiRequest('/cart', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId, quantity }),
    })
  },

  /**
   * Update cart item quantity
   */
  updateItem(cartItemId: number, quantity: number) {
    return apiRequest(`/cart/${cartItemId}`, {
      method: 'PUT',
      body: JSON.stringify({ quantity }),
    })
  },

  /**
   * Remove item from cart
   */
  removeItem(cartItemId: number) {
    return apiRequest(`/cart/${cartItemId}`, {
      method: 'DELETE',
    })
  },

  /**
   * Clear entire cart
   */
  clear() {
    return apiRequest('/cart', {
      method: 'DELETE',
    })
  },
}

/**
 * Order API methods (requires authentication)
 */
export const orderApi = {
  /**
   * Get user's orders
   */
  getAll() {
    return apiRequest('/orders')
  },

  /**
   * Get a single order by ID
   */
  getById(id: number) {
    return apiRequest(`/orders/${id}`)
  },

  /**
   * Create a new order from cart
   */
  create(orderData: {
    shipping_address: Record<string, any>
    billing_address?: Record<string, any>
    notes?: string
  }) {
    return apiRequest('/orders', {
      method: 'POST',
      body: JSON.stringify(orderData),
    })
  },

  /**
   * Cancel an order
   */
  cancel(orderId: number) {
    return apiRequest(`/orders/${orderId}`, {
      method: 'DELETE',
    })
  },
}

