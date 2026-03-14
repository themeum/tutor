<?php
/**
 * Query Service Demo
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Icon;

?>
<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Query Service</h1>
	<p class="tutor-text-gray-600 tutor-mb-8">
		A TanStack Query-like service for data fetching and mutations using Alpine.reactive(). Provides automatic caching, loading states, and error handling.
	</p>

	<!-- useQuery Example -->
	<div class="tutor-mb-12">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">useQuery - Fetch Users</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Demonstrates data fetching with automatic caching and refetching capabilities. The query caches data for 30 seconds.
		</p>

		<div x-data="{
			lastFetchTime: null,
			cacheInfo: 'No cache',
			
			async fetchUsers() {
				// Random User API returns different users each time
				const response = await axios.get('https://randomuser.me/api/?results=6');
				this.lastFetchTime = new Date().toLocaleTimeString();
				// Transform the data to match our display format
				return response.data.results.map(user => ({
					id: user.login.uuid,
					name: `${user.name.first} ${user.name.last}`,
					email: user.email,
					username: user.login.username,
					website: user.location.city + ', ' + user.location.country
				}));
			},
			
			updateCacheInfo() {
				this.cacheInfo = TutorCore.query.getCacheInfo('users');
			},
			
			usersQuery: null,
			
			init() {
				this.usersQuery = TutorCore.query.useQuery(
					'users',
					() => this.fetchUsers(),
					{ staleTime: 30000 }
				);
				
				// Update cache info every second
				setInterval(() => this.updateCacheInfo(), 1000);
			}
		}">
			<div class="tutor-flex tutor-gap-3 tutor-mb-4">
				<button 
					@click="usersQuery.refetch()" 
					class="tutor-btn tutor-btn-primary"
					:disabled="usersQuery.isLoading"
					:class="{ 'tutor-btn-loading': usersQuery.isLoading }"
				>
					<span>Refetch Users</span>
				</button>
				<button 
					@click="TutorCore.query.invalidateQuery('users'); usersQuery.refetch();" 
					class="tutor-btn tutor-btn-outline"
				>
					Invalidate Cache
				</button>
			</div>

			<!-- Status Badges -->
			<div class="tutor-flex tutor-gap-2 tutor-mb-4 tutor-flex-wrap">
				<span x-show="usersQuery.isLoading" class="tutor-badge tutor-badge-primary" x-cloak>
					Loading
				</span>
				<span x-show="usersQuery.isFetching && !usersQuery.isLoading" class="tutor-badge tutor-badge-warning" x-cloak>
					Fetching
				</span>
				<span x-show="usersQuery.data && !usersQuery.error" class="tutor-badge tutor-badge-success" x-cloak>
					Success
				</span>
				<span x-show="usersQuery.error" class="tutor-badge tutor-badge-error" x-cloak>
					Error
				</span>
			</div>

			<!-- Loading State -->
			<template x-if="usersQuery.isLoading">
				<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-p-4 tutor-bg-gray-50 tutor-rounded-lg">
					<div class="tutor-spinner"></div>
					<span class="tutor-text-gray-600">Loading users...</span>
				</div>
			</template>

			<!-- Error State -->
			<template x-if="usersQuery.error">
				<div class="tutor-p-4 tutor-bg-red-50 tutor-border tutor-border-red-200 tutor-rounded-lg">
					<strong class="tutor-text-red-700">Error:</strong>
					<span class="tutor-text-red-600" x-text="usersQuery.error.message"></span>
				</div>
			</template>

			<!-- Data Display -->
			<template x-if="usersQuery.data">
				<div class="tutor-grid tutor-grid-cols-1 md:tutor-grid-cols-2 tutor-gap-4">
					<template x-for="user in usersQuery.data" :key="user.id">
						<div class="tutor-p-4 tutor-bg-gray-50 tutor-border tutor-border-gray-200 tutor-rounded-lg">
							<h3 class="tutor-font-semibold tutor-text-lg tutor-mb-2" x-text="user.name"></h3>
							<p class="tutor-text-gray-600 tutor-text-sm tutor-mb-1">
								<strong>Email:</strong> <span x-text="user.email"></span>
							</p>
							<p class="tutor-text-gray-600 tutor-text-sm tutor-mb-1">
								<strong>Username:</strong> <span x-text="user.username"></span>
							</p>
							<p class="tutor-text-gray-600 tutor-text-sm">
								<strong>Website:</strong> <span x-text="user.website"></span>
							</p>
						</div>
					</template>
				</div>
			</template>

			<!-- Cache Info -->
			<div class="tutor-mt-4 tutor-p-3 tutor-bg-blue-50 tutor-rounded-lg tutor-text-sm">
				<strong>Cache Status:</strong> 
				<span x-text="cacheInfo"></span>
				<br>
				<strong>Last Fetched:</strong> 
				<span x-text="lastFetchTime || 'Loading...'"></span>
				<span class="tutor-text-gray-500"> (Random User API returns different users each time!)</span>
			</div>
		</div>
	</div>

	<!-- useMutation Example -->
	<div class="tutor-mb-12">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">useMutation - Create User</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Demonstrates data mutation with success/error callbacks and automatic cache invalidation.
		</p>

		<div x-data="{
			formData: { name: '', email: '', username: '' },
			
			async createUser(userData) {
				const response = await axios.post('https://jsonplaceholder.typicode.com/users', userData);
				return response.data;
			},
			
			createMutation: null,
			
			init() {
				this.createMutation = TutorCore.query.useMutation(
					(userData) => this.createUser(userData),
					{
						onSuccess: (data) => {
							console.log('User created:', data);
							TutorCore.query.invalidateQuery('users');
						}
					}
				);
			},
			
			async handleSubmit() {
				try {
					await this.createMutation.mutate(this.formData);
					this.formData = { name: '', email: '', username: '' };
				} catch (err) {
					console.error('Failed to create user');
				}
			}
		}">
			<form @submit.prevent="handleSubmit" class="tutor-max-w-md">
				<!-- Name Field -->
				<div class="tutor-input-field tutor-mb-4">
					<label for="user-name" class="tutor-label tutor-label-required">Name</label>
					<div class="tutor-input-wrapper">
						<input 
							type="text"
							id="user-name"
							x-model="formData.name"
							placeholder="Enter user name"
							class="tutor-input"
							required
						>
					</div>
				</div>

				<!-- Email Field -->
				<div class="tutor-input-field tutor-mb-4">
					<label for="user-email" class="tutor-label tutor-label-required">Email</label>
					<div class="tutor-input-wrapper">
						<input 
							type="email"
							id="user-email"
							x-model="formData.email"
							placeholder="Enter email address"
							class="tutor-input"
							required
						>
					</div>
				</div>

				<!-- Username Field -->
				<div class="tutor-input-field tutor-mb-4">
					<label for="user-username" class="tutor-label tutor-label-required">Username</label>
					<div class="tutor-input-wrapper">
						<input 
							type="text"
							id="user-username"
							x-model="formData.username"
							placeholder="Enter username"
							class="tutor-input"
							required
						>
					</div>
				</div>

				<!-- Buttons -->
				<div class="tutor-flex tutor-gap-3">
					<button 
						type="submit" 
						class="tutor-btn tutor-btn-primary"
						:disabled="createMutation.isPending"
						:class="{ 'tutor-btn-loading': createMutation.isPending }"
					>
						<span x-show="!createMutation.isPending">Create User</span>
						<span x-show="createMutation.isPending" x-cloak>Creating...</span>
					</button>
					<button 
						type="button" 
						@click="createMutation.reset()"
						class="tutor-btn tutor-btn-outline"
						:disabled="createMutation.isPending"
					>
						Reset
					</button>
				</div>
			</form>

			<!-- Status Badges -->
			<div class="tutor-flex tutor-gap-2 tutor-mt-4 tutor-flex-wrap">
				<span x-show="createMutation.isPending" class="tutor-badge tutor-badge-primary" x-cloak>
					Pending
				</span>
				<span x-show="createMutation.isSuccess" class="tutor-badge tutor-badge-success" x-cloak>
					Success
				</span>
				<span x-show="createMutation.isError" class="tutor-badge tutor-badge-error" x-cloak>
					Error
				</span>
			</div>

			<!-- Success Message -->
			<template x-if="createMutation.isSuccess && createMutation.data">
				<div class="tutor-mt-4 tutor-p-4 tutor-bg-green-50 tutor-border tutor-border-green-200 tutor-rounded-lg">
					<strong class="tutor-text-green-700">Success!</strong> 
					<span class="tutor-text-green-600">User created with ID: </span>
					<span class="tutor-text-green-600 tutor-font-mono" x-text="createMutation.data.id"></span>
					<div class="tutor-mt-2 tutor-text-sm">
						<strong>Name:</strong> <span x-text="createMutation.data.name"></span><br>
						<strong>Email:</strong> <span x-text="createMutation.data.email"></span>
					</div>
				</div>
			</template>

			<!-- Error Message -->
			<template x-if="createMutation.isError && createMutation.error">
				<div class="tutor-mt-4 tutor-p-4 tutor-bg-red-50 tutor-border tutor-border-red-200 tutor-rounded-lg">
					<strong class="tutor-text-red-700">Mutation Error:</strong>
					<span class="tutor-text-red-600" x-text="createMutation.error.message"></span>
				</div>
			</template>
		</div>
	</div>

	<!-- API Reference -->
	<div class="tutor-mb-12">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">API Reference</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Complete reference for the Query service methods.
		</p>

		<div class="tutor-overflow-x-auto">
			<table class="tutor-table tutor-table-bordered">
				<thead>
					<tr>
						<th class="tutor-px-4 tutor-py-2">Method</th>
						<th class="tutor-px-4 tutor-py-2">Parameters</th>
						<th class="tutor-px-4 tutor-py-2">Returns</th>
						<th class="tutor-px-4 tutor-py-2">Description</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>useQuery(key, fn, options?)</code></td>
						<td class="tutor-px-4 tutor-py-2">
							key: string<br>
							fn: () => Promise<br>
							options?: QueryOptions
						</td>
						<td class="tutor-px-4 tutor-py-2">QueryState</td>
						<td class="tutor-px-4 tutor-py-2">Creates a query with caching and refetching.</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>useMutation(fn, options?)</code></td>
						<td class="tutor-px-4 tutor-py-2">
							fn: (vars) => Promise<br>
							options?: MutationOptions
						</td>
						<td class="tutor-px-4 tutor-py-2">MutationState</td>
						<td class="tutor-px-4 tutor-py-2">Creates a mutation for data modifications.</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>invalidateQuery(key)</code></td>
						<td class="tutor-px-4 tutor-py-2">key: string</td>
						<td class="tutor-px-4 tutor-py-2">void</td>
						<td class="tutor-px-4 tutor-py-2">Invalidates a query cache entry.</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>getCacheInfo(key)</code></td>
						<td class="tutor-px-4 tutor-py-2">key: string</td>
						<td class="tutor-px-4 tutor-py-2">string</td>
						<td class="tutor-px-4 tutor-py-2">Gets cache age information.</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>clearCache()</code></td>
						<td class="tutor-px-4 tutor-py-2">-</td>
						<td class="tutor-px-4 tutor-py-2">void</td>
						<td class="tutor-px-4 tutor-py-2">Clears all cached queries.</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Query State Properties -->
		<h3 class="tutor-text-lg tutor-font-semibold tutor-mt-6 tutor-mb-3">QueryState Properties</h3>
		<div class="tutor-overflow-x-auto">
			<table class="tutor-table tutor-table-bordered">
				<thead>
					<tr>
						<th class="tutor-px-4 tutor-py-2">Property</th>
						<th class="tutor-px-4 tutor-py-2">Type</th>
						<th class="tutor-px-4 tutor-py-2">Description</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>data</code></td>
						<td class="tutor-px-4 tutor-py-2">any | null</td>
						<td class="tutor-px-4 tutor-py-2">The fetched data</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>error</code></td>
						<td class="tutor-px-4 tutor-py-2">Error | null</td>
						<td class="tutor-px-4 tutor-py-2">Error object if fetch failed</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>isLoading</code></td>
						<td class="tutor-px-4 tutor-py-2">boolean</td>
						<td class="tutor-px-4 tutor-py-2">True during initial fetch</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>isFetching</code></td>
						<td class="tutor-px-4 tutor-py-2">boolean</td>
						<td class="tutor-px-4 tutor-py-2">True during any fetch</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>refetch()</code></td>
						<td class="tutor-px-4 tutor-py-2">function</td>
						<td class="tutor-px-4 tutor-py-2">Manually refetch data</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Mutation State Properties -->
		<h3 class="tutor-text-lg tutor-font-semibold tutor-mt-6 tutor-mb-3">MutationState Properties</h3>
		<div class="tutor-overflow-x-auto">
			<table class="tutor-table tutor-table-bordered">
				<thead>
					<tr>
						<th class="tutor-px-4 tutor-py-2">Property</th>
						<th class="tutor-px-4 tutor-py-2">Type</th>
						<th class="tutor-px-4 tutor-py-2">Description</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>data</code></td>
						<td class="tutor-px-4 tutor-py-2">any | null</td>
						<td class="tutor-px-4 tutor-py-2">The mutation result data</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>error</code></td>
						<td class="tutor-px-4 tutor-py-2">Error | null</td>
						<td class="tutor-px-4 tutor-py-2">Error object if mutation failed</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>isPending</code></td>
						<td class="tutor-px-4 tutor-py-2">boolean</td>
						<td class="tutor-px-4 tutor-py-2">True while mutation is in progress</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>isError</code></td>
						<td class="tutor-px-4 tutor-py-2">boolean</td>
						<td class="tutor-px-4 tutor-py-2">True if mutation failed</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>isSuccess</code></td>
						<td class="tutor-px-4 tutor-py-2">boolean</td>
						<td class="tutor-px-4 tutor-py-2">True if mutation succeeded</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>mutate(vars)</code></td>
						<td class="tutor-px-4 tutor-py-2">function</td>
						<td class="tutor-px-4 tutor-py-2">Execute the mutation</td>
					</tr>
					<tr>
						<td class="tutor-px-4 tutor-py-2"><code>reset()</code></td>
						<td class="tutor-px-4 tutor-py-2">function</td>
						<td class="tutor-px-4 tutor-py-2">Reset mutation state</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Usage Example -->
	<div class="tutor-mb-12">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Code Example</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			Example code showing how to use the Query service in your Alpine components.
		</p>

		<div class="tutor-p-4 tutor-bg-gray-900 tutor-rounded-lg tutor-overflow-x-auto">
			<pre class="tutor-text-gray-100 tutor-text-sm"><code>// In your Alpine component
Alpine.data('myComponent', () => ({
	usersQuery: null,
	createMutation: null,
  
	init() {
	// Create a query
	this.usersQuery = TutorCore.query.useQuery(
		'users',
		async () => {
		const response = await axios.get('/api/users');
		return response.data;
		},
		{ staleTime: 30000 }
	);
	
	// Create a mutation
	this.createMutation = TutorCore.query.useMutation(
		async (userData) => {
		const response = await axios.post('/api/users', userData);
		return response.data;
		},
		{
		onSuccess: () => {
			TutorCore.query.invalidateQuery('users');
		}
		}
	);
	}
}));</code></pre>
		</div>
	</div>
</section>
