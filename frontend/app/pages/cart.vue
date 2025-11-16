<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Shopping Cart</h1>

    <div v-if="pending" class="space-y-4">
      <div v-for="i in 3" :key="i" class="animate-pulse bg-gray-200 h-32 rounded-lg"></div>
    </div>

    <div v-else-if="cart?.items && cart.items.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Cart Items -->
      <div class="lg:col-span-2 space-y-4">
        <div
          v-for="item in cart.items"
          :key="item.id"
          class="flex gap-4 p-4 border rounded-lg"
        >
          <div class="w-24 h-24 bg-gray-200 rounded-lg flex-shrink-0"></div>
          <div class="flex-1">
            <h3 class="font-semibold">{{ item.product.name }}</h3>
            <p class="text-gray-600">${{ item.product.price }}</p>
            <div class="mt-2 flex items-center gap-2">
              <button
                @click="updateQuantity(item.id, item.quantity - 1)"
                class="px-2 py-1 border rounded"
                :disabled="item.quantity <= 1"
              >
                -
              </button>
              <span>{{ item.quantity }}</span>
              <button
                @click="updateQuantity(item.id, item.quantity + 1)"
                class="px-2 py-1 border rounded"
              >
                +
              </button>
            </div>
          </div>
          <div class="text-right">
            <p class="font-semibold">${{ item.subtotal }}</p>
            <button
              @click="removeItem(item.id)"
              class="mt-2 text-red-600 text-sm"
            >
              Remove
            </button>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="lg:col-span-1">
        <div class="p-6 border rounded-lg sticky top-4">
          <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
          <div class="space-y-2 mb-4">
            <div class="flex justify-between">
              <span>Subtotal</span>
              <span>${{ cart.subtotal }}</span>
            </div>
            <div class="flex justify-between">
              <span>Tax</span>
              <span>$0.00</span>
            </div>
            <div class="flex justify-between">
              <span>Shipping</span>
              <span>$0.00</span>
            </div>
            <div class="border-t pt-2 flex justify-between font-semibold">
              <span>Total</span>
              <span>${{ cart.total }}</span>
            </div>
          </div>
          <NuxtLink
            to="/checkout"
            class="block w-full text-center py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Proceed to Checkout
          </NuxtLink>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <p class="text-gray-500 mb-4">Your cart is empty</p>
      <NuxtLink to="/products" class="text-blue-600 hover:underline">
        Continue Shopping
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
// Fetch cart
const { data: cart, pending, refresh } = await useAsyncData('cart', () =>
  cartApi.get()
)

// Update item quantity
async function updateQuantity(cartItemId: number, quantity: number) {
  if (quantity < 1) return
  try {
    await cartApi.updateItem(cartItemId, quantity)
    await refresh()
  } catch (error) {
    console.error('Failed to update cart item:', error)
  }
}

// Remove item from cart
async function removeItem(cartItemId: number) {
  try {
    await cartApi.removeItem(cartItemId)
    await refresh()
  } catch (error) {
    console.error('Failed to remove cart item:', error)
  }
}

useHead({
  title: 'Shopping Cart - UraharaShop',
})
</script>

